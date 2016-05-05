<?php
// @author          Kameloh
// @lastUpdated     2016-04-27

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\SBCGetPassword\SBCGetPassword as SBCGetPassword;

class AdminSetupSubmit
{
    private $user_id = 0;
    private $ip_address = '';
    private $password1 = '';
    private $password2 = '';
    private $password3 = '';
    private $isready = 0;

    // Construct
    public function __construct(&$obj_array)
    {
        $method = 'AdminSetupSubmit->__construct()';

        // Initialize Objects
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // Initialize Vars
        $this->ip_address   = SBC::getIpAddress();
        $pass1              = '';
        $pass2              = '';
        $pass3              = '';

        // Get passwords
        $pass1              = SBCGetPassword::process($_POST['pass1']);
        $pass2              = SBCGetPassword::process($_POST['pass2']);
        $pass3              = SBCGetPassword::process($_POST['pass3']);

        // New passwords
        $this->password1    = password_hash($pass1,PASSWORD_DEFAULT);
        $this->password2    = password_hash($pass2,PASSWORD_DEFAULT);
        $this->password3    = password_hash($pass3,PASSWORD_DEFAULT);

        // Open Connection
        $db->open();

        // User Required
        $User->required($db);
        $user_id        = $User->getUserId();
        $this->user_id  = $user_id;
        if (!$User->isAdmin())
        {
            SBC::userError('Sorry, only administrators may access this page');
        }

        // Check if this administrator needs a password
        $this->checkPasswords($db);

        // Create New Password
        $this->createNewPassword($db);

        // Close Connection
        $db->close();

        // Header
        header('Location: https://www.sketchbook.cafe/');
        exit;
    }

    // Create New Password
    private function createNewPassword(&$db)
    {
        $method = 'AdminSetupSubmit->createNewPassword()';

        // Is Ready?
        $this->isReady();

        // Initialize Vars
        $user_id    = $this->user_id;
        $password1  = $this->password1;
        $password2  = $this->password2;
        $password3  = $this->password3;

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Update admin's password
        $sql = 'UPDATE admins
            SET password1=?, 
            password2=?,
            password3=?,
            haspass=1
            WHERE user_id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('sssi',$password1,$password2,$password3,$user_id);
        SBC::statementFetchRow($stmt,$db,$sql,$method);
   }

    // Is ready
    private function isReady()
    {
        $method = 'AdminSetupSubmit->isReady()';

        if ($this->isready != 1 || $this->user_id < 1 || empty($this->password1) || empty($this->password2) || empty($this->password3))
        {
            SBC::devError('$isready is not set',$method);
        }
    }

    // Check Passwords
    private function checkPasswords(&$db)
    {
        $method = 'AdminSetupSubmit->checkPasswords()';

        // Initialize Vars
        $user_id = $this->user_id;

        // Doubly Check
        if ($user_id < 1)
        {
            SBC::devError('$user_id is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get admin information
        $sql = 'SELECT id, haspass
            FROM admins
            WHERE user_id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$user_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Admin ID?
        $admin_id = isset($row['id']) ? (int) $row['id'] : 0;
        if ($admin_id < 1)
        {
            SBC::devError('Could not find administrator in database',$method);
        }

        // Does the admin already have a password set up?
        if ($row['haspass'] > 0)
        {
            SBC::userError('Admin already has a password setup.');
        }

        // Set as ready
        $this->isready = 1;
    }
}