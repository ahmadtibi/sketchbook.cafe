<?php
// @author          Kameloh
// @lastUpdated     2016-05-19
namespace SketchbookCafe\ForumThreadRobot;

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\Message\Message as Message;
use SketchbookCafe\TextareaSettings\TextareaSettings as TextareaSettings;
use SketchbookCafe\TableForumThread\TableForumThread as TableForumThread;
use SketchbookCafe\ForumOrganizer\ForumOrganizer as ForumOrganizer;

class ForumThreadRobot
{
    private $rd = 0;
    private $time = 0;
    private $ip_address = '';

    private $user_id = 0;
    private $forum_id = 0;

    private $title = '';
    private $title_code = '';
    private $messageObj;

    private $comment_id = 0;
    private $thread_id = 0;

    private $db;

    // Construct
    public function __construct(&$db)
    {
        $this->db           = &$db;
        $this->rd           = SBC::rd();
        $this->time         = SBC::getTime();
        $this->ip_address   = SBC::getIpAddress();
    }

    // Set Thread Owner ID
    final public function setUserId($user_id)
    {
        $method = 'ForumThreadRobot->setUserId()';

        $this->user_id = isset($user_id) ? (int) $user_id : 0;
        if ($this->user_id < 1)
        {
            SBC::devError('User ID is not set',$method);
        }
    }

    // Set Forum ID
    final public function setForumId($forum_id)
    {
        $method = 'ForumThreadRobot->setForumId()';

        $this->forum_id = isset($forum_id) ? (int) $forum_id : 0;
        if ($this->forum_id < 1)
        {
            SBC::devError('Forum ID is not set',$method);
        }
    }

    // Set Title
    final public function setTitle($title)
    {
        $method = 'ForumThreadRobot->setTitle()';

        $titleObj = new Message(array
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
        $titleObj->insert($title);

        // Set Vars
        $this->title        = $titleObj->getMessage();
        $this->title_code   = $titleObj->getMessageCode();
    }

    // Set Message
    final public function setMessage($message)
    {
        $method = 'ForumThreadRobot->setMessage()';

        $settingsObj        = new TextareaSettings('forum_thread');
        $this->messageObj   = new Message($settingsObj->getSettings());
        $this->messageObj->insert($message);
    }

    // Verify Forum
    final private function verifyForum($forum_id)
    {
        $method = 'ForumThreadRobot->verifyForum()';

        // Initialize
        $db = &$this->db;
        if ($forum_id < 1)
        {
            SBC::devError('Forum ID is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get forum info
        $sql = 'SELECT id, isforum, isdeleted
            FROM forums
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$forum_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Verify
        $forum_id   = isset($row['id']) ? (int) $row['id'] : 0;
        if ($forum_id < 1)
        {
            SBC::devError('Could not verify forum',$method);
        }

        // Is it a forum?
        if ($row['isforum'] != 1)
        {
            SBC::devError('This is not a forum('.$forum_id.')',$method);
        }

        // Deleted?
        if ($row['isdeleted'] == 1)
        {
            SBC::devError('Forum no longer exists',$method);
        }
    }

    // Process
    final public function process()
    {
        $method = 'ForumThreadRobot->process()';

        // Initialize
        $db         = &$this->db;
        $rd         = $this->rd;
        $time       = $this->time;
        $ip_address = $this->ip_address;
        $forum_id   = $this->forum_id;
        $user_id    = $this->user_id;
        $title      = $this->title;
        $title_code = $this->title_code;
        $messageObj = &$this->messageObj;
        $table_name = 'forum'.$forum_id.'x';
        if ($user_id < 1)
        {
            SBC::devError('User ID is not set',$method);
        }
        if (empty($title))
        {
            SBC::devError('Title is not set',$method);
        }
        if (empty($messageObj))
        {
            SBC::devError('Message is not set',$method);
        }

        // Verify Forum
        $this->verifyForum($forum_id);

        // Create New Message
        $messageObj->setUserId($user_id);
        $messageObj->setType('forum_message');
        $messageObj->createMessage($db);
        $comment_id = $messageObj->getCommentId();
        $this->comment_id = $comment_id;

        // Switch
        $db->sql_Switch('sketchbookcafe');

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
        $thread_id  = isset($row['id']) ? (int) $row['id'] : 0;
        if ($thread_id < 1)
        {
            SBC::devError('Could not insert new thread into database',$method);
        }
        $this->thread_id = $thread_id;

        // Update message's parent_id as thread_id
        $messageObj->setParentId($thread_id);
        $messageObj->updateParentId($db);

        // Create Thread Table
        $TableForumThread = new TableForumThread($thread_id);
        $TableForumThread->checkTables($db);

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Mark thread as not deleted
        $sql = 'UPDATE forum_threads
            SET isdeleted=0
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$thread_id);
        SBC::statementExecute($stmt,$db,$sql,$method);

        // Switch
        $db->sql_switch('sketchbookcafe_forums');

        // Insert into forum's table
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

        // Forum Organizer
        $ForumOrganizer = new ForumOrganizer($db);
        $ForumOrganizer->countTotalThreads($forum_id);
        $ForumOrganizer->updateLastPostInfo($forum_id);
    }

    // Get Thread ID
    final public function getThreadId()
    {
        $method = 'ForumThreadRobot->getThreadId()';

        if ($this->thread_id < 1)
        {
            SBC::devError('Thread ID is not set',$method);
        }

        return $this->thread_id;
    }
}