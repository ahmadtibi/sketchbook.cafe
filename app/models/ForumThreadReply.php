<?php
// @author          Jonathan Maltezo (Kameloh)
// @lastUpdated     2016-04-27

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\TextareaSettings\TextareaSettings as TextareaSettings;
use SketchbookCafe\ForumOrganizer\ForumOrganizer as ForumOrganizer;
use SketchbookCafe\StatsOrganizer\StatsOrganizer as StatsOrganizer;

class ForumThreadReply
{
    private $user_id = 0;
    private $ip_address = 0;
    private $time = 0;

    private $rd = 0;
    private $thread_id = 0;
    private $thread_user_id = 0;

    private $forum_id = 0;
    private $category_id = 0;

    private $comment_id = 0;

    // Construct
    public function __construct(&$obj_array)
    {
        // Initialize Objects
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // Classes and Functions
        sbc_class('TextareaSettings');
        sbc_class('ForumOrganizer');
        sbc_class('StatsOrganizer');
        sbc_class('Message');
        sbc_class('UserTimer');

        // Initialize Vars
        $this->time         = time();
        $this->ip_address   = $_SERVER['REMOTE_ADDR'];
        $this->rd           = rd();

        // Thread ID
        $this->thread_id    = isset($_POST['thread_id']) ? (int) $_POST['thread_id'] : 0;
        if ($this->thread_id < 1)
        {
            error('Dev error: $thread_id is not set for ForumThreadReply->construct()');
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
        $pageno = current_page($ppage,$total_comments);

        // Header
        header('Location: https://www.sketchbook.cafe/forum/thread/'.$this->thread_id.'/'.$pageno.'/#recent');
        exit;
    }

    // Get Thread Information
    final private function getThreadInfo(&$db)
    {
        // Classes and Functions
        sbc_function('check_number');

        // Initialize Vars
        $statement_method   = 'ForumThreadReply->getThreadInfo';
        $thread_id          = check_number($this->thread_id,'thread_id');

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Thread Information
        $sql = 'SELECT id, forum_id, user_id, is_locked, isdeleted
            FROM forum_threads
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$thread_id);
        if (!$stmt->execute())
        {
            statement_error('get thread info',$statement_method);
        }
        $result     = $stmt->get_result();
        $thread_row = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        $stmt->close();

        // Check Thread
        $thread_id  = isset($thread_row['id']) ? (int) $thread_row['id'] : 0;
        if ($thread_id < 1)
        {
            error('Could not find thread in database');
        }

        // Is it locked?
        if ($thread_row['is_locked'] == 1)
        {
            error('Thread is locked and it cannot be replied to');
        }

        // Set User ID for Block Checks
        $this->thread_user_id = $thread_row['user_id'];

        // Forum ID
        $forum_id   = $thread_row['forum_id'];

        // Get Forum Information
        $sql = 'SELECT id, parent_id, isdeleted
            FROM forums
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$forum_id);
        if (!$stmt->execute())
        {
            statement_error('get forum info',$statement_method);
        }
        $result     = $stmt->get_result();
        $forum_row  = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        $stmt->close();

        // Check Forum
        $forum_id   = isset($forum_row['id']) ? (int) $forum_row['id'] : 0;
        if ($forum_id < 1)
        {
            error('Thread does not belong to a forum');
        }

        // Make sure forum isn't deleted
        if ($forum_row['isdeleted'] == 1)
        {
            error('Forum for thread no longer exists. Please ask an administrator if you want this thread moved.');
        }

        // Parent ID
        $category_id    = $forum_row['parent_id'];

        // Get Category Information
        $sql = 'SELECT id, isdeleted
            FROM forums
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$category_id);
        if (!$stmt->execute())
        {
            statement_error('get category info',$statement_method);
        }
        $result         = $stmt->get_result();
        $category_row   = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        $stmt->close();

        // Check
        $category_id    = isset($category_row['id']) ? (int) $category_row['id'] : 0;
        if ($category_id < 1)
        {
            error('Thread\'s forum does not have a category set');
        }

        // Deleted?
        if ($category_row['isdeleted'] == 1)
        {
            error('Thread\'s category no longer exists. Please contact an admiministrator');
        }

        // Set Vars
        $this->forum_id     = $forum_id;
        $this->category_id  = $category_id;
    }

    // Check Block
    final private function checkBlocked(&$db)
    {
        // Classes
        sbc_class('BlockCheck');

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
        // Classes and Functions
        sbc_function('check_number');
        sbc_function('check_empty');

        // Initialize Vars
        $thread_id  = check_number($this->thread_id,'thread_id');
        $user_id    = check_number($this->user_id,'user_id');
        $time       = check_number($this->time,'time');
        $ip_address = check_empty($this->ip_address,'ip_address');

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
        // Classes and Functions
        sbc_function('check_number');

        // Initialize Vars
        $statement_method   = 'ForumThreadReply->insertIntoTable()';
        $thread_id          = check_number($this->thread_id,'thread_id');
        $comment_id         = check_number($this->comment_id,'comment_id');
        $user_id            = check_number($this->user_id,'user_id');

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
        if (!$stmt->execute())
        {
            statement_error('insert comment',$statement_method);
        }
        $stmt->close();
    }

    // Update Thread's Timer
    final private function updateThread(&$db)
    {
        // Initiailize Vars
        $time       = time();
        $thread_id  = $this->thread_id;
        $forum_id   = $this->forum_id;
        $method     = 'ForumThreadReply->updateThread()';
        if ($thread_id < 1)
        {
            error('Dev error: $thread_id('.$thread_id.') or $forum_id('.$forum_id.') is not set for '.$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Update Thread's Date Updated
        $sql = 'UPDATE forum_threads
            SET date_updated=?,
            date_bumped=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('iii',$time,$time,$thread_id);
        if (!$stmt->execute())
        {
            statement_error('update thread timer',$method);
        }
        $stmt->close();

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
        if (!$stmt->execute())
        {
            statement_error('update forum table',$method);
        }
        $stmt->close();
    }
}