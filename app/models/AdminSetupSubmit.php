<?php

class AdminSetupSubmit
{
    private $user_id = 0;
    private $ip_address = '';
    private $password1 = '';
    private $password2 = '';
    private $password3 = '';
    private $isready = 0;

    // Construct
    public function __construct()
    {
        // Classes and Functions
        sbc_function('get_password');

        // Initialize Vars
        $this->ip_address   = $_SERVER['REMOTE_ADDR'];
        $pass1              = '';
        $pass2              = '';
        $pass3              = '';

        // Get passwords
        $pass1              = get_password($_POST['pass1']);
        $pass2              = get_password($_POST['pass2']);
        $pass3              = get_password($_POST['pass3']);

        // New passwords
        $this->password1    = password_hash($pass1,PASSWORD_DEFAULT);
        $this->password2    = password_hash($pass2,PASSWORD_DEFAULT);
        $this->password3    = password_hash($pass3,PASSWORD_DEFAULT);

        // Globals
        global $db,$User;

        // Open Connection
        $db->open();

        // User Required
        $User->required($db);
        $user_id        = $User->getUserId();
        $this->user_id  = $user_id;
        if (!$User->isAdmin())
        {
            error('Sorry, only administrators may access this page');
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
        if (!$stmt->execute())
        {
            error('Could not execute statement (update admin password) for AdminSetupSubmit->createNewPassword()');
        }
        $stmt->close();
   }

    // Is ready
    private function isReady()
    {
        if ($this->isready != 1 || $this->user_id < 1 || empty($this->password1) || empty($this->password2) || empty($this->password3))
        {
            error('Dev error: $isready is not set for AdminSetupSubmit->isReady()');
        }
    }

    // Check Passwords
    private function checkPasswords(&$db)
    {
        // Initialize Vars
        $user_id = $this->user_id;

        // Doubly Check
        if ($user_id < 1)
        {
            error('Dev error: $user_id is not set for AdminSetupSubmit->checkPasswords()');
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get admin information
        $sql = 'SELECT id, haspass
            FROM admins
            WHERE user_id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$user_id);
        if (!$stmt->execute())
        {
            error('Could not execute statement (get admin information) for AdminSetupSubmit->checkPasswords()');
        }
        $result = $stmt->get_result();
        $row    = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        $stmt->close();

        // Admin ID?
        $admin_id = isset($row['id']) ? (int) $row['id'] : 0;
        if ($admin_id < 1)
        {
            error('Dev error: Could not find administrator in database for AdminSetupSubmit->checkPasswords()');
        }

        // Does the admin already have a password set up?
        if ($row['haspass'] > 0)
        {
            error('Admin already has a password setup.');
        }

        // Set as ready
        $this->isready = 1;
    }
}