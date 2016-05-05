<?php
// @author          Kameloh
// @lastUpdated     2016-04-27

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\SBCGetUsername\SBCGetUsername as SBCGetUsername;
use SketchbookCafe\ForumOrganizer\ForumOrganizer as ForumOrganizer;

class AdminForumAdminsSubmit
{
    // $User
    private $ip_addres = '';
    private $time = 0;
    private $user_id = 0;

    // Forum
    private $forum_id = 0;

    // Admin vars
    private $admin_user_id = 0;
    private $username = '';

    // Construct
    public function __construct(&$obj_array)
    {
        $method = 'AdminForumAdminsSubmit->__construct()';

        // Initialize Objects and Vars
        $db                 = &$obj_array['db'];
        $User               = &$obj_array['User'];
        $this->ip_address   = SBC::getIpAddress();
        $this->time         = SBC::getTime();

        // Forum ID
        $this->forum_id = isset($_POST['forum_id']) ? (int) $_POST['forum_id'] : 0;
        if ($this->forum_id < 1)
        {
            SBC::devError('$forum_id is not set',$method);
        }

        // Username
        $this->username = SBCGetUsername::process($_POST['username']);

        // Open Connection
        $db->open();

        // Admin Required 
        $User->admin($db);
        $User->requireAdminFlag('manage_forum_admins');
        $this->user_id = $User->getUserId();

        // Get User Info
        $this->getUserInfo($db);

        // Get Forum Info
        $this->getForumInfo($db);

        // Add Admin
        $this->addAdmin($db);

        // Update Admin Array for Forum
        $this->updateForumAdmins($db);

        // Close Connection
        $db->close();

        // Header
        header('Location: https://www.sketchbook.cafe/admin/manage_forum_admins/');
        exit;
    }

    // Get User Info
    final private function getUserInfo(&$db)
    {
        $method = 'AdminForumAdminsSubmit->getUserInfo()';

        // Initialize Vars
        $username   = $this->username;

        // Check
        if (empty($username))
        {
            SBC::devError('$username not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get user information
        $sql = 'SELECT id
            FROM users
            WHERE username=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('s',$username);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Check
        $this->admin_user_id = isset($row['id']) ? (int) $row['id'] : 0;
        if ($this->admin_user_id < 1)
        {
            SBC::userError('Could not find user in database');
        }
    }

    // Get Forum Information
    final private function getForumInfo(&$db)
    {
        $method = 'AdminForumAdminsSubmit->getForumInfo()';

        // Initialize Vars
        $forum_id   = $this->forum_id;

        // Check
        if ($forum_id < 1)
        {
            SBC::devError('$forum_id is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get forum information
        $sql = 'SELECT id, isforum
            FROM forums
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$forum_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Check
        $forum_id   = isset($row['id']) ? (int) $row['id'] : 0;
        if ($forum_id < 1)
        {
            SBC::userError('Could not find forum in database');
        }
    }

    // Add Admin
    final private function addAdmin(&$db)
    {
        $method = 'AdminForumAdminsSubmit->addAdmin()';

        // Initialize Vars
        $time           = $this->time;
        $ip_address     = $this->ip_address;
        $user_id        = $this->user_id;
        $admin_user_id  = $this->admin_user_id;
        $forum_id       = $this->forum_id;
        $method         = 'AdminForumAdminsSubmit->addAdmin()';

        // Check
        if ($admin_user_id < 1 || $forum_id < 1 || $user_id < 1)
        {
            SBC::devError('$admin_user_id:'.$admin_user_id.', $forum_id:'.$forum_id.', $user_id:'.$user_id,$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Check if they're already an admin in the forum
        $sql = 'SELECT id
            FROM forum_admins
            WHERE user_id=?
            AND forum_id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('ii',$admin_user_id,$forum_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Does the ID exist?
        $id = isset($row['id']) ? (int) $row['id'] : 0;
        if ($id < 1)
        {
            // Add Admin
            $sql = 'INSERT INTO forum_admins
                SET user_id=?,
                forum_id=?,
                user_id_created=?,
                date_created=?,
                ip_created=?';
            $stmt = $db->prepare($sql);
            $stmt->bind_param('iiiis',$admin_user_id,$forum_id,$user_id,$time,$ip_address);
            SBC::statementExecute($stmt,$db,$sql,$method);
        }
    }

    // Update Forum Admin Array
    final private function updateForumAdmins(&$db)
    {
        $method = 'AdminForumAdminsSubmit->updateForumAdmins()';

        // Initialize Vars
        $forum_id   = $this->forum_id;
        if ($forum_id < 1)
        {
            SBC::devError('$forum_id is not set',$method);
        }

        // Forum Organizer: Update Admin Array
        $ForumOrganizer = new ForumOrganizer($db);
        $ForumOrganizer->forumAdminUpdateArray($forum_id);
    }
}