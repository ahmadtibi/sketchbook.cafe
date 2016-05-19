<?php
// @author          Kameloh
// @lastUpdated     2016-05-02

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\TextareaSettings\TextareaSettings as TextareaSettings;
use SketchbookCafe\ForumOrganizer\ForumOrganizer as ForumOrganizer;
use SketchbookCafe\StatsOrganizer\StatsOrganizer as StatsOrganizer;
use SketchbookCafe\Message\Message as Message;
use SketchbookCafe\UserTimer\UserTimer as UserTimer;
use SketchbookCafe\BlockCheck\BlockCheck as BlockCheck;

class ForumThreadReply
{
    private $user_id = 0;
    private $ip_address = 0;
    private $time = 0;

    private $rd = 0;
    private $thread_id = 0;
    private $thread_user_id = 0;
    private $bump_date = 0;

    private $forum_id = 0;
    private $category_id = 0;

    private $comment_id = 0;

    // Construct
    public function __construct(&$obj_array)
    {
        // Initialize Objects
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // Initialize Vars
        $this->time         = SBC::getTime();
        $this->ip_address   = SBC::getIpAddress();
        $this->rd           = SBC::rd();

        // Thread ID
        $this->thread_id    = isset($_POST['thread_id']) ? (int) $_POST['thread_id'] : 0;
        if ($this->thread_id < 1)
        {
            SBC::devError('$thread_id is not set',$method);
        }

        // Textarea Settings
        $TextareaSettings   = new TextareaSettings('forum_reply');
        $message_settings   = $TextareaSettings->getSettings();

        // Message
        $messageObject      = new Message($message_settings);
        $messageObject->insert($_POST['message']);

        // Open Connection
        $db->open();

        // User Required
        $User->required($db);
        $this->user_id = $User->getUserId();

        // User Timer
        $UserTimer = new UserTimer(array
        (
            'user_id'   => $this->user_id,
        ));
        $UserTimer->setColumn('forum_reply');
        $UserTimer->checkTimer($db);

        // Get Thread Information
        $this->getThreadInfo($db);

        // Block Check
        $this->checkBlocked($db);

        // Create New Message
        $this->createReply($db,$messageObject);

        // Insert into the thread's table
        $this->insertIntoTable($db);

        // Update Thread Timer
        $this->updateThread($db);

        // Forum Organizer
        $ForumOrganizer = new ForumOrganizer($db);

        // Count Unique Comments
        $ForumOrganizer->threadUniqueComments($this->thread_id);

        // Count Total Replies
        $ForumOrganizer->threadTotalReplies($this->thread_id);

        // Update Last Info for Forum Thread
        $ForumOrganizer->threadUpdateInfo($this->thread_id);

        // Update Bump Timer for Thread
        $ForumOrganizer->threadUpdateBumpDate($this->thread_id);

        // Get Total Comments
        $total_comments = $ForumOrganizer->threadGetTotalComments($this->thread_id);

        // Add Total Posts for Forum
        $ForumOrganizer->forumTotalPostsAddOne($this->forum_id);

        // Update Forum Last Post
        $ForumOrganizer->forumUpdateInfo($this->forum_id);

        // Add One Post for Category
        $ForumOrganizer->categoryTotalPostsAddOne($this->category_id);

        // StatsOrganizer
        $StatsOrganizer = new StatsOrganizer($db);

        // Add Total Posts for User
        $StatsOrganizer->userForumPostAdd($this->user_id);

        // Update User Timer
        $UserTimer->update($db);

        // Close Connection
        $db->close();

        // Calculate Page
        $ppage  = 10;
        $total_comments -= 1; // subtract one since the forumorganizer adds +1 for the forum thread's post
        if ($total_comments < 1)
        {
            $total_comments = 0;
        }
        $pageno = SBC::currentPage($ppage,$total_comments);

        // Header
        header('Location: https://www.sketchbook.cafe/forum/thread/'.$this->thread_id.'/'.$pageno.'/#recent');
        exit;
    }

    // Get Thread Information
    final private function getThreadInfo(&$db)
    {
        $method = 'ForumThreadReply->getThreadInfo()';

        // Initialize Vars
        $thread_id = SBC::checkNumber($this->thread_id,'thread_id');

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Thread Information
        $sql = 'SELECT id, forum_id, user_id, is_locked, is_sticky, isdeleted
            FROM forum_threads
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$thread_id);
        $thread_row = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Check Thread
        $thread_id  = isset($thread_row['id']) ? (int) $thread_row['id'] : 0;
        if ($thread_id < 1)
        {
            SBC::userError('Could not find thread in database');
        }

        // Is it locked?
        if ($thread_row['is_locked'] == 1)
        {
            SBC::userError('Thread is locked and it cannot be replied to');
        }

        // Set User ID for Block Checks
        $this->thread_user_id = $thread_row['user_id'];

        // Bump Date
        $this->bump_date  = $this->time;
        if ($thread_row['is_sticky'] == 1)
        {
            // Stickied threads are always 10 years ahead
            $this->bump_date  += 315360000;
        }

        // Forum ID
        $forum_id   = $thread_row['forum_id'];

        // Get Forum Information
        $sql = 'SELECT id, parent_id, isdeleted
            FROM forums
            WHERE id=?
            LIMIT 1';
        $stmt       = $db->prepare($sql);
        $stmt->bind_param('i',$forum_id);
        $forum_row  = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Check Forum
        $forum_id   = isset($forum_row['id']) ? (int) $forum_row['id'] : 0;
        if ($forum_id < 1)
        {
            SBC::userError('Thread does not belong to a forum');
        }

        // Make sure forum isn't deleted
        if ($forum_row['isdeleted'] == 1)
        {
            SBC::userError('Forum for thread no longer exists. Please ask an administrator if you want this thread moved.');
        }

        // Parent ID
        $category_id    = $forum_row['parent_id'];

        // Get Category Information
        $sql = 'SELECT id, isdeleted
            FROM forums
            WHERE id=?
            LIMIT 1';
        $stmt           = $db->prepare($sql);
        $stmt->bind_param('i',$category_id);
        $category_row   = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Check
        $category_id    = isset($category_row['id']) ? (int) $category_row['id'] : 0;
        if ($category_id < 1)
        {
            SBC::userError('Thread\'s forum does not have a category set');
        }

        // Deleted?
        if ($category_row['isdeleted'] == 1)
        {
            SBC::userError('Thread\'s category no longer exists. Please contact an admiministrator');
        }

        // Set Vars
        $this->forum_id     = $forum_id;
        $this->category_id  = $category_id;
    }

    // Check Block
    final private function checkBlocked(&$db)
    {
        $method = 'ForumThreadReply->checkBlocked()';

        // Initialize Vars
        $user_id    = $this->user_id;
        $r_user_id  = $this->thread_user_id;

        // Check if they're blocking each other
        $BlockCheck = new BlockCheck(array
        (
            'user_id'   => $user_id,
            'r_user_id' => $r_user_id,
        ));
        $BlockCheck->check($db);
    }

    // Create Message
    final private function createReply(&$db,&$messageObject)
    {
        $method = 'ForumThreadReply->createReply()';

        // Initialize Vars
        $thread_id  = SBC::checkNumber($this->thread_id,'thread_id');
        $user_id    = SBC::checkNumber($this->user_id,'user_id');
        $time       = SBC::checkNumber($this->time,'time');
        $ip_address = SBC::checkEmpty($this->ip_address,'ip_address');

        // Create New Message
        $messageObject->setUserId($user_id);
        $messageObject->setType('forum_thread_reply');
        $messageObject->createMessage($db);
        $messageObject->setParentId($thread_id);
        $messageObject->updateParentId($db);
        $comment_id = $messageObject->getCommentId();

        // Set
        $this->comment_id = $comment_id;
    }

    // Insert Comment into Thread's Table
    final private function insertIntoTable(&$db)
    {
        $method = 'ForumThreadReply->insertIntoTable()';

        // Initialize Vars
        $thread_id          = SBC::checkEmpty($this->thread_id,'thread_id');
        $comment_id         = SBC::checkEmpty($this->comment_id,'comment_id');
        $user_id            = SBC::checkEmpty($this->user_id,'user_id');

        // Switch
        $db->sql_switch('sketchbookcafe_forums');

        // Table
        $table_name = 't'.$thread_id.'d';

        // Insert comment
        $sql = 'INSERT INTO '.$table_name.'
            SET cid=?,
            uid=?';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ii',$comment_id,$user_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }

    // Update Thread's Timer
    final private function updateThread(&$db)
    {
        $method = 'ForumThreadReply->updateThread()';

        // Initiailize Vars
        $time       = SBC::getTime();
        $user_id    = $this->user_id;
        $thread_id  = $this->thread_id;
        $forum_id   = $this->forum_id;
        $bump_date  = $this->bump_date;

        if ($thread_id < 1)
        {
            SBC::devError('$thread_id('.$thread_id.') or $forum_id('.$forum_id.') is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Update Thread's Date Updated
        $sql = 'UPDATE forum_threads
            SET date_updated=?,
            date_bumped=?,
            last_user_id=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('iiii',$time,$bump_date,$user_id,$thread_id);
        SBC::statementExecute($stmt,$db,$sql,$method);

        // Switch
        $db->sql_switch('sketchbookcafe_forums');

        // Forum Table
        $table_name = 'forum'.$forum_id.'x';

        // Update Forum
        $sql = 'UPDATE '.$table_name.'
            SET date_updated=?,
            date_bumped=?
            WHERE thread_id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('iii',$time,$time,$thread_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }
}