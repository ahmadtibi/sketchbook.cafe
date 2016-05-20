<?php
// @author          Kameloh
// @lastUpdated     2016-05-20

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\ForumAdmin\ForumAdmin as ForumAdmin;

class ForumThreadLockPost
{
    private $user_id = 0;
    private $comment_id = 0;
    private $thread_id = 0;
    private $hasinfo = 0;
    private $is_locked = 0;

    private $obj_array = [];

    // Construct
    public function __construct(&$obj_array)
    {
        $method = 'CommentPostLock->__construct()';

        $this->obj_array = &$obj_array;
    }

    // Set Comment ID
    final public function setCommentId($comment_id)
    {
        $method = 'CommentPostLock->setCommentId()';

        // Initialize
        $this->comment_id = isset($comment_id) ? (int) $comment_id : 0;
        if ($this->comment_id < 1)
        {
            SBC::devError('Comment ID is not set',$method);
        }

        // Set
        $this->hasinfo = 1;
    }

    // Has Info
    final private function hasInfo()
    {
        $method = 'CommentPostLock->hasInfo()';

        if ($this->hasinfo != 1)
        {
            SBC::devError('hasinfo is not set',$method);
        }
    }

    // Process
    final public function process()
    {
        $method = 'CommentPostLock->process()';

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
        $this->user_id = $User->getUserId();

        // Forum Admin
        $ForumAdmin = new ForumAdmin($this->obj_array);
        $ForumAdmin->setUserId($this->user_id);
        $ForumAdmin->getForumInfoByCommentId($comment_id);
        $ForumAdmin->process();
        $ForumAdmin->requireFlag('lock_post');

        // Get Comment Information
        $this->getComment($db);

        // Set Lock
        $this->setLock($db);

        // Close Connection
        $db->close();
    }

    // Get Comment - for verifying if it's a forum thread or post
    final private function getComment(&$db)
    {
        $method = 'CommentPostLock->getComment()';

        // Has Info
        $this->hasInfo();

        // Initialize
        $comment_id = $this->comment_id;

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Comment Info
        $sql = 'SELECT id, parent_id, type, is_locked, isdeleted
            FROM sbc_comments
            WHERE id=?
            LIMIT 1';
       
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$comment_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Verify
        $comment_id = isset($row['id']) ? (int) $row['id'] : 0;
        if ($comment_id < 1)
        {
            SBC::devError('Cannot find comment in database',$method);
        }

        // Deleted?
        if ($row['isdeleted'] == 1)
        {
            SBC::userError('Comment no longer exists');
        }

        // Type must be 2 (thread) or 3 (forum post)
        if ($row['type'] != 2)
        {
            if ($row['type'] != 3)
            {
                SBC::userError('Sorry, this is not a valid forum comment');
            }
        }

        // Set
        $this->thread_id    = $row['parent_id'];
        $this->is_locked    = $row['is_locked'];
    }

    // Set Post Lock
    final private function setLock(&$db)
    {
        $method = 'CommentPostLock->setLock()';

        // Has Info
        $this->hasInfo();

        // Initialize
        $comment_id = $this->comment_id;
        $is_locked  = $this->is_locked;

        // Switcharoo
        if ($is_locked == 1)
        {
            $is_locked = 0;
        }
        else
        {
            $is_locked = 1;
        }
        $this->is_locked = $is_locked;

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Update comment
        $sql = 'UPDATE sbc_comments
            SET is_locked=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ii',$is_locked,$comment_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }

    // Get Locked
    final public function getIsLocked()
    {
        return $this->is_locked;
    }
}