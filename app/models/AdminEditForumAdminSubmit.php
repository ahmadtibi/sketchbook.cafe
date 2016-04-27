<?php
// @author          Jonathan Maltezo (Kameloh)
// @lastUpdated     2016-04-27

use SketchbookCafe\SBC\SBC as SBC;

class AdminEditForumAdminSubmit
{
    private $admin_id = 0;

    // Flags
    private $lock_thread = 0;
    private $lock_post = 0;
    private $bump_thread = 0;
    private $move_thread = 0;
    private $sticky_thread = 0;
    private $edit_thread = 0;
    private $edit_post = 0;

    // Construct
    public function __construct(&$obj_array)
    {
        $method = 'AdminEditForumAdminSubmit->__construct()';

        // Initialize Objects
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // Set Admin ID
        $this->admin_id = isset($_POST['admin_id']) ? (int) $_POST['admin_id'] : 0;
        if ($this->admin_id < 1)
        {
            SBC::devError('$admin_id is not set',$method);
        }

        // Fields: lock_thread, lock_post, bump_thread, move_thread,
        //    sticky_thread, edit_thread, edit_post

        // Get Flags
        $this->lock_thread      = SBC::oneZero($_POST['lock_thread']);
        $this->lock_post        = SBC::oneZero($_POST['lock_post']);
        $this->bump_thread      = SBC::oneZero($_POST['bump_thread']);
        $this->move_thread      = SBC::oneZero($_POST['move_thread']);
        $this->sticky_thread    = SBC::oneZero($_POST['sticky_thread']);
        $this->edit_thread      = SBC::oneZero($_POST['edit_thread']);
        $this->edit_post        = SBC::oneZero($_POST['edit_post']);

        // Open Connection
        $db->open();

        // Admin Required + Process Data
        $User->admin($db);
        $User->requireAdminFlag('manage_forum_admins');

        // Get Admin Info
        $this->getAdminInfo($db);

        // Update Admin Flags
        $this->updateAdminFlags($db);

        // Close Connection
        $db->close();

        // Header
        header('Location: https://www.sketchbook.cafe/admin/edit_forum_admin/'.$this->admin_id.'/');
        exit;
    }

    // Get Admin Info
    final private function getAdminInfo(&$db)
    {
        $method = 'AdminEditForumAdminSubmit->getAdminInfo()';

        // Initialize Vars
        $admin_id   = $this->admin_id;

        // Check
        if ($admin_id < 1)
        {
            SBC::devError('$admin_id is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Adnmin Info
        $sql = 'SELECT id
            FROM forum_admins
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$admin_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Check
        $admin_id   = isset($row['id']) ? (int) $row['id'] : 0;
        if ($admin_id < 1)
        {
            SBC::userError('Could not find Forum Admin ID in database');
        }
    }

    // Update Admin Flags
    final private function updateAdminFlags(&$db)
    {
        $method = 'AdminEditForumAdminSubmit->updateAdminFlags';

        // Initialize Vars
        $admin_id       = $this->admin_id;
        $lock_thread    = $this->lock_thread;
        $lock_post      = $this->lock_post;
        $bump_thread    = $this->bump_thread;
        $move_thread    = $this->move_thread;
        $sticky_thread  = $this->sticky_thread;
        $edit_thread    = $this->edit_thread;
        $edit_post      = $this->edit_post;

        // Check
        if ($admin_id < 1)
        {
            SBC::devError('$admin_id is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Update Forum Admin
        $sql = 'UPDATE forum_admins
            SET lock_thread=?,
            lock_post=?,
            bump_thread=?,
            move_thread=?,
            sticky_thread=?,
            edit_thread=?,
            edit_post=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('iiiiiiii',$lock_thread,$lock_post,$bump_thread,$move_thread,$sticky_thread,$edit_thread,$edit_post,$admin_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }
}