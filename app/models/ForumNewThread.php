<?php

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

    // Construct
    public function __construct(&$obj_array)
    {
        // Initialize Objects
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // Classes and Functions
        sbc_class('UserTimer');
        sbc_class('Message');
        sbc_class('TextareaSettings');
        sbc_function('rd');

        // Initialize Vars
        $this->ip_address   = $_SERVER['REMOTE_ADDR'];
        $this->time         = time();
        $this->rd           = rd();

        // Forum ID
        $this->forum_id = isset($_POST['forum_id']) ? (int) $_POST['forum_id'] : 0;
        if ($this->forum_id < 1)
        {
            error('Dev error: $forum_id is not set for ForumNewThread->construct()');
        }

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

        // Mark Thread as undeleted
        $this->updateThread($db);

        // Insert Into Forum's Table
        $this->insertThread($db);

        // Count Total Threads for Forums (fix this)

        // Count User Threads (fix this)

        // Update General Category (fix this)

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
        // Initialize Vars
        $forum_id = $this->forum_id;
        if ($forum_id < 1)
        {
            error('Dev error: $forum_id is not set for ForumNewThread->checkForum()');
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Forum Information
        $sql = 'SELECT id, isforum, isdeleted
            FROM forums
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$forum_id);
        if (!$stmt->execute())
        {
            error('Could not execute statement (get forum information) for ForumNewThread->checkForum()');
        }
        $result = $stmt->get_result();
        $row    = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        $stmt->close();

        // Check
        $forum_id   = isset($row['id']) ? (int) $row['id'] : 0;
        if ($forum_id < 1)
        {
            error('Could not find forum in database');
        }

        // Is it a forum?
        if ($row['isforum'] != 1)
        {
            error('Sorry, you cannot make a new thread in a category');
        }

        // Is it deleted?
        if ($row['isdeleted'] == 1)
        {
            error('Forum no longer exists');
        }
    }

    // Create Thread
    final private function createThread(&$db,&$messageObject)
    {
        // Classes and Functions
        sbc_function('check_number');
        sbc_function('check_empty');

        // Initialize Vars
        $forum_id   = check_number($this->forum_id,'forum_id');
        $rd         = check_number($this->rd,'rd');
        $user_id    = check_number($this->user_id,'user_id');
        $time       = check_number($this->time,'time');
        $ip_address = check_empty($this->ip_address,'ip_address');

        // String Vars
        $title      = check_empty($this->title,'title');
        $title_code = check_empty($this->title_code,'title_code');

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
        if (!$stmt->execute())
        {
            error('Could not execute statement (insert new forum thread) for ForumNewThread->createThread()');
        }
        $stmt->close();

        // Get Thread ID
        $sql = 'SELECT id
            FROM forum_threads
            WHERE rd=?
            AND forum_id=?
            AND user_id=?
            AND date_created=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('iiii',$rd,$forum_id,$user_id,$time);
        if (!$stmt->execute())
        {
            error('Could not execute statement (get thread id) for ForumNewThread->createThread()');
        }
        $result = $stmt->get_result();
        $row    = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        $stmt->close();

        // Thread ID
        $thread_id = isset($row['id']) ? (int) $row['id'] : 0;
        if ($thread_id < 1)
        {
            error('Dev error: could not insert new thread for ForumNewThread->createThread()');
        }
        $this->thread_id = $thread_id;
    }

    // Create Thread Table
    private function createThreadTable(&$db)
    {
        // Initialize Vars
        $thread_id  = $this->thread_id;
        if ($thread_id < 1)
        {
            error('Dev error: $thread_id is not set for ForumNewThread->createThreadTable()');
        }

        // Classes and Functions
        sbc_class('TableForumThread');

        // Generate Tables
        $TableForumThread = new TableForumThread($thread_id);
        $TableForumThread->checkTables($db);
    }

    // Update Thread
    private function updateThread(&$db)
    {
        // Initialize Vars
        $thread_id  = $this->thread_id;
        if ($thread_id < 1)
        {
            error('Dev error: $thread_id is not set for ForumNewThread->updateThread()');
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Update
        $sql = 'UPDATE forum_threads
            SET isdeleted=0
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$thread_id);
        if (!$stmt->execute())
        {
            error('Could not execute statement (update thread) for ForumNewThread->updateThread()');
        }
        $stmt->close();
    }

    // Insert into the forum's table
    private function insertThread(&$db)
    {
        // Functions
        sbc_function('check_number');

        // Initialize Vars
        $comment_id = check_number($this->comment_id,'comment_id');
        $user_id    = check_number($this->user_id,'user_id');
        $thread_id  = check_number($this->thread_id,'thread_id');
        $forum_id   = check_number($this->forum_id,'forum_id');
        $time       = check_number($this->time,'time');

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
        if (!$stmt->execute())
        {
            error('Could not execute statement (insert thread into forum) for ForumNewThread->insertThread()');
        }
        $stmt->close();
    }
}