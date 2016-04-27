<?php
// @author          Jonathan Maltezo (Kameloh)
// @lastUpdated     2016-04-27

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\SBCGetUsername\SBCGetUsername as SBCGetUsername;
use SketchbookCafe\TableUser\TableUser as TableUser;

class AdminFixUserTableSubmit
{
    private $user_id = 0;
    private $username = '';

    // Construct
    public function __construct(&$obj_array)
    {
        $method = 'AdminFixUserTableSubmit->__construct()';

        // Initialize Objects
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // Username
        $this->username = SBCGetUsername::process($_POST['username']);

        // Open Connection
        $db->open();

        // Admin Required + Process Data
        $User->admin($db);
        $User->requireAdminFlag('fix_user_table');

        // Get User Info
        $this->getUserInfo($db);

        // Fix User's Tables
        $this->fixUserTables($db);

        // Close Connection
        $db->close();

        // Header
        header('Location: https://www.sketchbook.cafe/admin/fix_user_table/1/');
        exit;
    }

    // Get User Information
    private function getUserInfo(&$db)
    {
        $method = 'AdminFixUserTableSubmit->getUserInfo()';

        // Initialize Vars
        $username   = $this->username;

        // Check
        if (empty($username))
        {
            SBC::devError('$username is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get User Info
        $sql = 'SELECT id 
            FROM users
            WHERE username=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('s',$username);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Check?
        $user_id    = isset($row['id']) ? (int) $row['id'] : 0;
        if ($user_id < 1)
        {
            error('Could not find user in database');
        }

        // Set vars
        $this->user_id = $user_id;
    }

    // Fix User's Tables
    private function fixUserTables(&$db)
    {
        $method = 'AdminFixUserTableSubmit->fixUserTables()';

        // Initialize Vars
        $user_id    = $this->user_id;

        // Check
        if ($user_id < 1)
        {
            SBC::devError('$user_id is not set',$method);
        }

        // Table User
        $TableUser  = new TableUser($user_id);
        $TableUser->checkTables($db);
    }
}