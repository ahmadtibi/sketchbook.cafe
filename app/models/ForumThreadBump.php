<?php
// @author          Kameloh
// @lastUpdated     2016-04-29

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\ForumAdmin\ForumAdmin as ForumAdmin;

class ForumThreadBump
{
    private $comment_id = 0;
    private $thread_id = 0;
    private $hasinfo = 0;
    private $time = 0;

    private $obj_array = [];

    // Construct
    public function __construct(&$obj_array)
    {
        $method = 'ForumThreadBump->__construct()';

        // Set
        $this->obj_array    = &$obj_array;
        $this->time         = SBC::getTime();
    }

    // Set Comment
    final public function setCommentId($comment_id)
    {
        $method = 'ForumThreadBump->setCommentId()';

        // Set
        $this->comment_id = isset($comment_id) ? (int) $comment_id : 0;
        if ($this->comment_id < 1)
        {
            SBC::devError('$comment_id is not set',$method);
        }

        $this->hasinfo = 1;
    }

    // Has Info
    final public function hasInfo()
    {
        $method = 'ForumThreadBump->hasInfo()';

        if ($this->hasinfo != 1)
        {
            SBC::devError('hasinfo is not set',$method);
        }
    }

    // Process
    final public function process()
    {
        $method = 'ForumThreadBump->process()';

        // Has Info
        $this->hasInfo();

        // Initialize
        $db         = &$this->obj_array['db'];
        $User       = &$this->obj_array['User'];
        $comment_id = $this->comment_id;

        // Open Connection
        $db->open();

        // User Required
        $User->required($db);

        // Forum Admin
        $ForumAdmin = new ForumAdmin($this->obj_array);
        $ForumAdmin->setUserId($User->getUserId());
        $ForumAdmin->getForumInfoByCommentId($this->comment_id);
        $ForumAdmin->process();
        $ForumAdmin->requireFlag('bump_thread');

        $this->thread_id = $ForumAdmin->getThreadId();

        // Bump Thread
        $this->bumpThread($db);

        // Close Connection
        $db->close();

        // Header
        header('Location: https://www.sketchbook.cafe/forum/thread/'.$this->thread_id.'/');
        exit;
    }

    // Bump Forum Thread
    final private function bumpThread(&$db)
    {
        $method = 'ForumThreadBump->bumpThread()';

        // Set
        $thread_id  = SBC::checkNumber($this->thread_id,'$thread_id');
        $time       = $this->time;

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Thread Info
        $sql = 'SELECT id, forum_id, is_sticky, isdeleted
            FROM forum_threads
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$thread_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Check
        $thread_id  = isset($row['id']) ? (int) $row['id'] : 0;
        if ($thread_id < 1)
        {
            SBC::devError('Cannot find thread',$method);
        }

        // Stickied Threads should have +10 years
        if ($row['is_sticky'] == 1)
        {
            $time += 315360000; // 10 years
        }

        // Forum ID
        $forum_id   = $row['forum_id'];

        // Verify Forum Information
        $sql = 'SELECT id 
            FROM forums
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$forum_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Check
        $forum_id = isset($row['id']) ? (int) $row['id'] : 0;
        if ($forum_id < 1)
        {
            SBC::devError('Cannot find forum',$method);
        }

        // Bump Thread Info
        $sql = 'UPDATE forum_threads
            SET date_bumped=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ii',$time,$thread_id);
        SBC::statementExecute($stmt,$db,$sql,$method);

        // Switch
        $db->sql_switch('sketchbookcafe_forums');

        // Table
        $table = 'forum'.$forum_id.'x';

        // Update Forum Info
        $sql = 'UPDATE '.$table.' 
            SET date_bumped=?
            WHERE thread_id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ii',$time,$thread_id);
        SBC::statementExecute($stmt,$db,$sql,$method);

        // Forum Organizer? (FIXME)
    }
}