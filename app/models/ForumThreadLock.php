<?php
// @author          Kameloh
// @lastUpdated     2016-04-29

use SketchbookCafe\SBC\SBC as SBC;

class ForumThreadLock
{
    private $comment_id = 0;
    private $thread_id = 0;
    private $is_locked = 0;
    private $hasinfo = 0;

    private $obj_array = [];

    // Construct
    public function __construct(&$obj_array)
    {
        $method = 'ForumThreadLock->__construct()';

        // Set
        $this->obj_array = &$obj_array;
    }

    // Set Comment
    final public function setCommentId($comment_id)
    {
        $method = 'ForumThreadLock->setCommentId()';

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
        $method = 'ForumThreadLock->hasInfo()';

        if ($this->hasinfo != 1)
        {
            SBC::devError('hasinfo is not set',$method);
        }
    }

    // Process
    final public function process()
    {
        $method = 'ForumThreadLock->process()';

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

        // Get Forum ID
        $forum_id   = $this->getForumId($db);

        // Get Forum Flags
        $User->getForumAdminFlags($db,$forum_id);

        // Forum Admin
        if (!$User->isForumAdmin())
        {
            SBC::userError('Sorry, only forum admins may access this page');
        }

        // Do we have a forum flag?
        if (!$User->hasForumAdminFlag('lock_thread'))
        {
            SBC::userError('Sorry, you do not have the necessary permissions to perform this action');
        }

        // Lock or Unlock Thread, Depending
        $this->setLock($db);

        // Close Connection
        $db->close();

        // Header
        header('Location: https://www.sketchbook.cafe/forum/thread/'.$this->thread_id.'/');
        exit;
    }

    // Get Forum ID
    final private function getForumId(&$db)
    {
        $method = 'ForumThreadLock->getForumId()';

        // Has Info
        $this->hasInfo();

        // Initialize
        $comment_id = $this->comment_id;

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Comment Info
        $sql = 'SELECT id, type, parent_id
            FROM sbc_comments
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$comment_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Check
        $comment_id = isset($row['id']) ? (int) $row['id'] : 0;
        if ($comment_id < 1)
        {
            SBC::devError('cannot find comment in database',$method);
        }

        // Type Check
        if ($row['type'] != 2)
        {
            SBC::devError('this comment is not a thread',$method);
        }

        // Thread ID
        $thread_id  = $row['parent_id'];

        // Get Forum ID
        $sql = 'SELECT id, forum_id, is_locked
            FROM forum_threads
            WHERE id=?
            LIMIT 1';
        $stmt       = $db->prepare($sql);
        $stmt->bind_param('i',$thread_id);
        $thread_row = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Check
        $thread_id  = isset($thread_row['id']) ? (int) $thread_row['id'] : 0;
        if ($thread_id < 1)
        {
            SBC::devError('Cannot find thread in database('.$thread_id.')',$method);
        }

        // Set
        $this->thread_id = $thread_id;
        $this->is_locked = $thread_row['is_locked'];

        // Return Forum ID
        return $thread_row['forum_id'];
    }

    // Lock or Unlock Thread
    final private function setLock(&$db)
    {
        $method = 'ForumThreadLock->setLock()';

        // Initialize
        $thread_id  = SBC::checkNumber($this->thread_id,'thread_id');
        $is_locked  = SBC::oneZero($this->is_locked,'is_locked');

        // Lock or Unlock?
        if ($is_locked == 1)
        {
            $is_locked = 0;
        }
        else
        {
            $is_locked = 1;
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Update Thread
        $sql = 'UPDATE forum_threads
            SET is_locked=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ii',$is_locked,$thread_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }
}