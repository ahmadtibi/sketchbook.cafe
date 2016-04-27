<?php
// @author          Jonathan Maltezo (Kameloh)
// @lastUpdated     2016-04-27
namespace SketchbookCafe\ForumAdmin;

use SketchbookCafe\SBC\SBC as SBC;

class ForumAdmin
{
    private $user_id = 0;
    private $forum_id = 0;

    // Generated
    private $hasinfo = 0;
    private $admin_id = 0;
    private $isadmin = 0;
    private $flag = [];

    // Construct
    public function __construct($user_id,$forum_id)
    {
        $method = 'ForumAdmin->__construct()';

        // Set
        $this->user_id  = isset($user_id) ? (int) $user_id : 0;
        $this->forum_id = isset($forum_id) ? (int) $forum_id : 0;

        // Check
        if ($this->user_id < 1)
        {
            SBC::devError('$user_id is not set',$method);
        }
        if ($this->forum_id < 1)
        {
            SBC::devError('$forum_id is not set',$method);
        }

        // Set
        $this->hasinfo = 1;
    }

    // Has Info
    final private function hasInfo()
    {
        $method = 'ForumAdmin->hasInfo()';

        if ($this->hasinfo != 1)
        {
            SBC::devError('$hasinfo is not set',$method);
        }
    }

    // Is Admin
    final public function isAdmin()
    {
        if ($this->isadmin == 1)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    // Process Admin: get flags
    final public function process(&$db)
    {
        $method = 'ForumAdmin->process()';

        // Has Info
        $this->hasInfo();

        // Initialize Vars
        $user_id    = $this->user_id;
        $forum_id   = $this->forum_id;
        $method     = 'ForumAdmin->process()';

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Forum Admin
        $sql = 'SELECT id, lock_thread, lock_post, bump_thread, move_thread, sticky_thread, edit_thread, edit_post
            FROM forum_admins
            WHERE user_id=?
            AND forum_id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('ii',$user_id,$forum_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Check
        $this->admin_id = isset($row['id']) ? (int) $row['id'] : 0;
        if ($this->admin_id < 1)
        {
            // Exit
            return null;
        }

        // Set Flags
        $this->isadmin  = 1;
        $this->flag     = $row;
    }
}