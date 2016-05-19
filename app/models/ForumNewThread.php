<?php
// @author          Kameloh
// @lastUpdated     2016-05-08

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\UserTimer\UserTimer as UserTimer;
use SketchbookCafe\Message\Message as Message;
use SketchbookCafe\TextareaSettings\TextareaSettings as TextareaSettings;
use SketchbookCafe\ForumOrganizer\ForumOrganizer as ForumOrganizer;
use SketchbookCafe\TableForumThread\TableForumThread as TableForumThread;
use SketchbookCafe\TableForumPoll\TableForumPoll as TableForumPoll;
use SketchbookCafe\ThreadOrganizer\ThreadOrganizer as ThreadOrganizer;

class ForumNewThread
{
    private $thread_id = 0;
    private $forum_id;
    private $ip_address = '';
    private $time = 0;
    private $user_id = 0;
    private $rd = 0;

    private $comment_id = 0;

    private $title = '';
    private $title_code = '';
    private $message = '';
    private $message_code = '';

    private $pollMessage = [];
    private $pollMessageCode = [];

    private $has_poll = 0;
    private $poll_id = 0;

    // Construct
    public function __construct(&$obj_array)
    {
        $method = 'ForumNewThread->__construct()';

        // Initialize Objects
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // Initialize Vars
        $this->ip_address   = SBC::getIpAddress();
        $this->time         = SBC::getTime();
        $this->rd           = SBC::rd();

        // Forum ID
        $this->forum_id = isset($_POST['forum_id']) ? (int) $_POST['forum_id'] : 0;
        if ($this->forum_id < 1)
        {
            SBC::devError('$forum_id is not set',$method);
        }

        // Get Polls
        $i = 1;
        while ($i < 11)
        {
            // Poll Object
            $pollObject[$i] = new Message(array
            (
                'name'          => 'poll'.$i,
                'min'           => 0,
                'column_max'    => 250,
            ));

            // Insert
            $pollObject[$i]->insert($_POST['poll'.$i]);

            // Set Messages
            $this->pollMessage[$i]      = $pollObject[$i]->getMessage();
            $this->pollMessageCode[$i]  = $pollObject[$i]->getMessageCode();

            $i++;
        }

        // Check if a poll is set
        $this->pollCheck();

        // Thread Title
        $titleObject        = new Message(array
        (
            'name'          => 'name',
            'min'           => 1,
            'column_max'    => 250,
            'nl2br'         => 0,
            'basic'         => 0,
            'ajax'          => 0,
            'images'        => 0,
            'videos'        => 0,
        ));
        $titleObject->insert($_POST['name']);

        // Textarea Settings
        $TextareaSettings = new TextareaSettings('forum_thread');
        $TextareaSettings->setValue('');
        $message_settings   = $TextareaSettings->getSettings();

        // Message
        $messageObject  = new Message($message_settings);
        $messageObject->insert($_POST['message']);

        // SQL Vars
        $this->title        = $titleObject->getMessage();
        $this->title_code   = $titleObject->getMessageCode();

        // Open Connection
        $db->open();

        // User Required
        $User->required($db);
        $user_id = $User->getUserId();
        $this->user_id = $user_id;

        // User Timer
        $UserTimer  = new UserTimer(array
        (
            'user_id'   => $user_id,
        ));
        $UserTimer->setColumn('new_forum_thread');
        $UserTimer->checkTimer($db);

        // Check if the forum exists
        $this->checkForum($db);

        // Create Thread
        $this->createThread($db,$messageObject);

        // Create Thread Table
        $this->createThreadTable($db);

        // Create Poll if it exists
        $this->createPoll($db);

        // Mark Thread as undeleted
        $this->updateThread($db);

        // Insert Into Forum's Table
        $this->insertThread($db);

        // Forum Organizer
        $ForumOrganizer = new ForumOrganizer($db);
        $ForumOrganizer->countTotalThreads($this->forum_id);
        $ForumOrganizer->updateLastPostInfo($this->forum_id);

        // Thread Organizer
        $ThreadOrganizer = new ThreadOrganizer($db);
        $ThreadOrganizer->countUniqueUsers($this->thread_id);

        // Count User Threads (fix this)

        // ==== Do These Last

        // User Timer
        $UserTimer->update($db);

        // Close Connection
        $db->close();

        // Header
        header('Location: https://www.sketchbook.cafe/forum/thread/'.$this->thread_id.'/');
        exit;
    }

    // Check Forum
    final private function checkForum(&$db)
    {
        $method = 'ForumNewThread->checkForum()';

        // Initialize Vars
        $forum_id = $this->forum_id;
        if ($forum_id < 1)
        {
            SBC::devError('$forum_id is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Forum Information
        $sql = 'SELECT id, isforum, isdeleted
            FROM forums
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$forum_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Check
        $forum_id   = isset($row['id']) ? (int) $row['id'] : 0;
        if ($forum_id < 1)
        {
            SBC::userError('Could not find forum in database');
        }

        // Is it a forum?
        if ($row['isforum'] != 1)
        {
            SBC::userError('Sorry, you cannot make a new thread in a category');
        }

        // Is it deleted?
        if ($row['isdeleted'] == 1)
        {
            SBC::userError('Forum no longer exists');
        }
    }

    // Create Thread
    final private function createThread(&$db,&$messageObject)
    {
        $method = 'ForumNewThread->createThread()';

        // Initialize Vars
        $forum_id   = SBC::checkNumber($this->forum_id,'forum_id');
        $rd         = SBC::checkNumber($this->rd,'rd');
        $user_id    = SBC::checkNumber($this->user_id,'user_id');
        $time       = SBC::checkNumber($this->time,'time');
        $ip_address = SBC::checkEmpty($this->ip_address,'ip_address');

        // String Vars
        $title      = SBC::checkEmpty($this->title,'title');
        $title_code = SBC::checkEmpty($this->title_code,'title_code');

        // Create New Message
        $messageObject->setUserId($user_id);
        $messageObject->setType('forum_message');
        $messageObject->createMessage($db);
        $comment_id = $messageObject->getCommentId();
        $this->comment_id = $comment_id;

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Create New Thread
        $sql = 'INSERT INTO forum_threads
            SET rd=?,
            forum_id=?,
            user_id=?,
            ip_created=?,
            ip_updated=?,
            date_created=?,
            date_updated=?,
            date_bumped=?,
            comment_id=?,
            title=?,
            title_code=?,
            last_user_id=?,
            last_comment_id=?,
            isdeleted=1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('iiissiiiissii',$rd,$forum_id,$user_id,$ip_address,$ip_address,$time,$time,$time,$comment_id,$title,$title_code,$user_id,$comment_id);
        SBC::statementExecute($stmt,$db,$sql,$method);

        // Get Thread ID
        $sql = 'SELECT id
            FROM forum_threads
            WHERE rd=?
            AND forum_id=?
            AND user_id=?
            AND date_created=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('iiii',$rd,$forum_id,$user_id,$time);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Thread ID
        $thread_id = isset($row['id']) ? (int) $row['id'] : 0;
        if ($thread_id < 1)
        {
            SBC::devError('could not insert new thread',$method);
        }
        $this->thread_id = $thread_id;

        // Update the message's parent_id as thread_id
        $messageObject->setParentId($thread_id);
        $messageObject->updateParentId($db);
    }

    // Create Thread Table
    private function createThreadTable(&$db)
    {
        $method = 'ForumNewThread->createThreadTable()';

        // Initialize Vars
        $thread_id  = $this->thread_id;
        if ($thread_id < 1)
        {
            SBC::devError('$thread_id is not set',$method);
        }

        // Generate Tables
        $TableForumThread = new TableForumThread($thread_id);
        $TableForumThread->checkTables($db);
    }

    // Update Thread
    private function updateThread(&$db)
    {
        $method = 'ForumNewThread->updateThread()';

        // Initialize Vars
        $thread_id  = $this->thread_id;
        $poll_id    = $this->poll_id;
        if ($thread_id < 1)
        {
            SBC::devError('$thread_id is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Update
        $sql = 'UPDATE forum_threads
            SET poll_id=?,
            isdeleted=0
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ii',$poll_id,$thread_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }

    // Insert into the forum's table
    private function insertThread(&$db)
    {
        $method = 'ForumNewThread->insertThread()';

        // Initialize Vars
        $comment_id = SBC::checkNumber($this->comment_id,'comment_id');
        $user_id    = SBC::checkNumber($this->user_id,'user_id');
        $thread_id  = SBC::checkNumber($this->thread_id,'thread_id');
        $forum_id   = SBC::checkNumber($this->forum_id,'forum_id');
        $time       = SBC::checkNumber($this->time,'time');

        // Switch
        $db->sql_switch('sketchbookcafe_forums');

        // Table name
        $table_name = 'forum'.$forum_id.'x';

        // Insert
        $sql = 'INSERT INTO '.$table_name.'
            SET thread_id=?,
            date_created=?, 
            date_updated=?,
            date_bumped=?,
            user_id=?,
            last_user_id=?,
            last_comment_id=?';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('iiiiiii',$thread_id,$time,$time,$time,$user_id,$user_id,$comment_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }

    // Poll Check
    final private function pollCheck()
    {
        $method = 'ForumNewThread->pollCheck()';

        // Check Array
        $i = 1;
        while ($i < 11)
        {
            if (!empty($this->pollMessage[$i]))
            {
                $this->has_poll = 1;
            }

            $i++;
        }
    }

    // Create Poll
    final private function createPoll(&$db)
    {
        $method = 'ForumNewThread->createPoll()';

        // Check
        if ($this->has_poll != 1)
        {
            return null;
        }

        // Initialize (sql vars)
        $rd             = SBC::rd();
        $ip_address     = SBC::getIpAddress();
        $time           = SBC::getTime();
        $thread_id      = SBC::checkNumber($this->thread_id,'$this->thread_id');
        $user_id        = SBC::checkNumber($this->user_id,'$this->user_id');
        $i = 1;
        while ($i < 11)
        {
            $message[$i]        = $this->pollMessage[$i];
            // $messageCode[$i]    = $this->pollMessageCode[$i];
            $i++;
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Insert New Poll
        $sql = 'INSERT INTO forum_polls
            SET rd=?,
            thread_id=?,
            user_id=?,
            date_created=?,
            date_updated=?,
            ip_created=?,
            ip_updated=?,
            isdeleted=1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('iiiiiss',$rd,$thread_id,$user_id,$time,$time,$ip_address,$ip_address);
        SBC::statementExecute($stmt,$db,$sql,$method);

        // Get Poll ID
        $sql = 'SELECT id
            FROM forum_polls
            WHERE rd=?
            AND thread_id=?
            AND date_created=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('iii',$rd,$thread_id,$time);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Poll ID
        $poll_id    = isset($row['id']) ? (int) $row['id'] : 0;
        if ($poll_id < 1)
        {
            SBC::devError('Could not insert new poll into database',$method);
        }

        // Update Poll with Messages
        $sql = 'UPDATE forum_polls
            SET 
            message1=?,
            message2=?,
            message3=?,
            message4=?,
            message5=?,
            message6=?,
            message7=?,
            message8=?,
            message9=?,
            message10=?,
            isdeleted=0
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ssssssssssi',$message[1],$message[2],$message[3],$message[4],$message[5],$message[6],$message[7],$message[8],$message[9],$message[10],$poll_id);
        SBC::statementExecute($stmt,$db,$sql,$method);

        // Create Poll Tables
        $TableForumPoll = new TableForumPoll($poll_id);
        $TableForumPoll->checkTables($db);

        // Set Poll ID
        $this->poll_id = $poll_id;
    }
}