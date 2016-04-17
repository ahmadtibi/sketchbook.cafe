<?php

class ChangePasswordSubmit
{
    private $user_id = 0;
    private $ip_address = '';
    private $new_password = '';

    // Construct
    public function __construct(&$obj_array)
    {
        // Initialize Objects
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // Classes and Functions
        sbc_class('UserTimer');
        sbc_class('LoginTimer');
        sbc_function('get_password');

        // Initialize Vars
        $this->ip_address   = $_SERVER['REMOTE_ADDR'];
        $pass1              = '';
        $pass2              = '';
        $new_password       = '';
        $current_password   = '';

        // New Password
        $pass1              = get_password($_POST['pass1']);
        $pass2              = get_password($_POST['pass2']);

        // Do they match?
        if ($pass1 != $pass2)
        {
            error('Passwords do not match');
        }

        // New Password
        $new_password       = password_hash($pass1,PASSWORD_DEFAULT);
        $this->new_password = $new_password;

        // Current Password
        $current_password   = get_password($_POST['current_password']);

        // Open Connection
        $db->open();

        // Required User + User ID
        $User->required($db);
        $user_id        = $User->getUserId();
        $this->user_id  = $user_id;

        // User Timer
        $UserTimer = new UserTimer(array
        (
            'user_id'   => $user_id, 
        ));
        $UserTimer->setColumn('change_password');
        $UserTimer->checkTimer($db);

        // Login Timer
        $LoginTimer = new LoginTimer();
        $LoginTimer->check($db);

        // Password Verification
        $check = $User->checkPasswordMatch($db,$current_password);
        if ($check !== true)
        {
            // Failed Login
            $LoginTimer->failedLogin($db);

            // Generate Error
            error('Invalid password verification.');
        }

        // Create Changed E-mail Log
        $this->createLog($db);

        // Set New Password
        $this->setNewPassword($db);

        // User Timer
        $UserTimer->update($db);

        // Close Connection
        $db->close();

        // Header
        header('Location: https://www.sketchbook.cafe/settings/changepassword/');
        exit;
    }

    // Create Log
    final private function createLog(&$db)
    {
        // Initialize Vars
        $user_id    = $this->user_id;
        $ip_address = $this->ip_address;
        $time       = time();

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Insert new log
        $sql = 'INSERT INTO log_password_change
            SET user_id=?, 
            ip_address=?,
            date_created=?';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('isi',$user_id,$ip_address,$time);
        if (!$stmt->execute())
        {
            error('Could not execute statement (insert new log) for ChangePasswordSubmit->createLog()');
        }
        $stmt->close();
    }

    // Set New Password
    final private function setNewPassword(&$db)
    {
        // Initialize Vars
        $user_id        = $this->user_id;
        $new_password   = $this->new_password;

        // Check
        if ($user_id < 1 || empty($new_password))
        {
            error('Dev error: $user_id or $new_password is not set. $user_id: '.$user_id);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Update password
        $sql = 'UPDATE users
            SET password=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('si',$new_password,$user_id);
        if (!$stmt->execute())
        {
            error('Could not execute statement (update password) for ChangePasswordSubmit->setNewPassword()');
        }
        $stmt->close();
    }
}