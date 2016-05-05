<?php
// @author          Kameloh
// @lastUpdated     2016-05-02
namespace SketchbookCafe\ForumAdmin;

use SketchbookCafe\SBC\SBC as SBC;

class ForumAdmin
{
    private $user_id = 0;
    private $forum_id = 0;
    private $thread_id = 0;
    private $comment_id = 0;
    private $forum_admin_id = 0; // ID of admin, not user_id
    private $forum_admin_flag = [];

    private $obj_array = [];

    // Construct
    public function __construct(&$obj_array)
    {
        $method = 'ForumAdmin->__construct()';

        // Set
        $this->obj_array = &$obj_array;
    }

    // Has Info : make sure we have $user_id and $forum_id
    final private function hasInfo()
    {
        $method = 'ForumAdmin->hasInfo()';

        if ($this->user_id < 1)
        {
            SBC::devError('$user_id is not set',$method);
        }
        if ($this->forum_id < 1)
        {
            SBC::devError('$forum_id is not set',$method);
        }
    }

    // Set User ID
    final public function setUserId($user_id)
    {
        $method = 'ForumAdmin->setUserId()';

        // Set
        $this->user_id  = isset($user_id) ? (int) $user_id : 0;
        if ($this->user_id < 1)
        {
            SBC::devError('$user_id is not set',$method);
        }
    }

    // Set Forum ID
    final public function setForumId($forum_id)
    {
        $method = 'ForumAdmin->setForumId()';

        // Set
        $this->forum_id = isset($forum_id) ? (int) $forum_id : 0;
        if ($this->forum_id < 1)
        {
            SBC::devError('$forum_id is not set',$method);
        }
    }

    // Get Forum Info based off Thread ID
    final public function getForumInfoByThreadId($thread_id)
    {
        $method = 'ForumAdmin->getForumInfoByThreadId()';

        // Initialize
        $db                 = &$this->obj_array['db'];
        $this->thread_id   = isset($thread_id) ? (int) $thread_id : 0;
        if ($this->thread_id < 1)
        {
            SBC::devError('$thread_id is not set',$method);
        }
        $thread_id          = $this->thread_id;

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Thread Information
        $sql = 'SELECT id, forum_id
            FROM forum_threads
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$thread_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Check Thread
        $this->thread_id  = isset($row['id']) ? (int) $row['id'] : 0;
        if ($this->thread_id < 1)
        {
            SBC::devError('Could not find forum thread in database (CID:'.$comment_id.')',$method);
        }

        // Set Forum ID
        $this->forum_id = $row['forum_id'];
        if ($this->forum_id < 1)
        {
            SBC::devError('Could not find forum ID based off forum thread ('.$this->thread_id.')',$method);
        }
    }

    // Get Forum Info based off Comment ID
    final public function getForumInfoByCommentId($comment_id)
    {
        $method = 'ForumAdmin->getForumInfoByCommentId()';

        // Initialize
        $db                 = &$this->obj_array['db'];
        $this->comment_id   = isset($comment_id) ? (int) $comment_id : 0;
        if ($this->comment_id < 1)
        {
            SBC::devError('$comment_id is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Comment Information
        $sql = 'SELECT id, type, parent_id
            FROM sbc_comments
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$comment_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Comment ID
        $comment_id = isset($row['id']) ? (int) $row['id'] : 0;
        if ($comment_id < 1)
        {
            SBC::devError('Cannot find comment ID in database',$method);
        }

        // Make sure type is 2 or 3
        if ($row['type'] != 2)
        {
            if ($row['type'] != 3)
            {
                SBC::userError('Sorry, this is not a thread comment');
            }
        }

        // Set Parent Thread ID
        $thread_id  = $row['parent_id'];

        // Get Thread Information
        $sql = 'SELECT id, forum_id
            FROM forum_threads
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$thread_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Check Thread
        $this->thread_id  = isset($row['id']) ? (int) $row['id'] : 0;
        if ($this->thread_id < 1)
        {
            SBC::devError('Could not find forum thread in database (CID:'.$comment_id.')',$method);
        }

        // Set Forum ID
        $this->forum_id = $row['forum_id'];
        if ($this->forum_id < 1)
        {
            SBC::devError('Could not find forum ID based off forum thread ('.$this->thread_id.')',$method);
        }
    }

    // Process: Get Forum Admin Flags
    final public function process()
    {
        $method = 'ForumAdmin->process()';

        // Has Info?
        $this->hasInfo();

        // Initialize
        $db         = &$this->obj_array['db'];
        $user_id    = $this->user_id;
        $forum_id   = $this->forum_id;

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Forum Admin
        $sql = 'SELECT *
            FROM forum_admins
            WHERE user_id=?
            AND forum_id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('ii',$user_id,$forum_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Check
        $this->forum_admin_id   = isset($row['id']) ? (int) $row['id'] : 0;
        if ($this->forum_admin_id < 1)
        {
            SBC::userError('Sorry, only forum admins may access this area');
        }

        // Set Flags
        $this->forum_admin_flag = $row;
    }

    // Require Flag
    final public function requireFlag($flag)
    {
        // Check
        $flag   = isset($this->forum_admin_flag[$flag]) ? $this->forum_admin_flag[$flag] : 0;
        if ($flag != 1)
        {
            SBC::userError('Sorry, you do not have the necessary permissions to perform this action');
        }
    }

    // Get Thread ID
    final public function getThreadId()
    {
        return $this->thread_id;
    }

    // Get Forum ID
    final public function getForumId()
    {
        return $this->forum_id;
    }
}