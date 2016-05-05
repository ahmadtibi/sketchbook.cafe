<?php
// @author          Kameloh
// @lastUpdated     2016-04-26

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\LoginTimer\LoginTimer as LoginTimer;
use SketchbookCafe\UserTimer\UserTimer as UserTimer;
use SketchbookCafe\SBCGetPassword\SBCGetPassword as SBCGetPassword;
use SketchbookCafe\SBCGetEmail\SBCGetEmail as SBCGetEmail;

class ChangeEmailSubmit
{
    private $user_id = 0;
    private $ip_address = '';
    private $time = '';
    private $new_email = '';
    private $old_email = '';

    // Construct
    public function __construct(&$obj_array)
    {
        $method = 'ChangeEmailSubmit->__construct()';

        // Initialize Objects
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // Initialize and Set Vars
        $this->ip_address   = SBC::getIpAddress();
        $this->time         = SBC::getTime();

        // E-mails
        $email1 = '';
        $email2 = '';
        $email1 = SBCGetEmail::process($_POST['email1']);
        $email2 = SBCGetEmail::process($_POST['email2']);

        // E-mails match?
        if ($email1 != $email2)
        {
            error('E-mails do not match');
        }

        // Set Vars
        $new_email          = $email1;
        $this->new_email    = $new_email;

        // Password
        $password = '';
        $password = SBCGetPassword::process($_POST['password']);

        // Open Connection
        $db->open();

        // Login Timer
        $LoginTimer = new LoginTimer();
        $LoginTimer->check($db);

        // Required User + User ID
        $User->required($db);
        $user_id        = $User->getUserId();
        $this->user_id  = $user_id;

        // User Timer
        $UserTimer = new UserTimer(array
        (
            'user_id'   => $user_id, 
        ));
        $UserTimer->setColumn('change_email');
        $UserTimer->checkTimer($db);

        // Password Verification
        $check = $User->checkPasswordMatch($db,$password);
        if ($check !== true)
        {
            // Failed Login
            $LoginTimer->failedLogin($db);

            // Generate Error
            SBC::userError('Invalid password verification.');
        }

        // Get Old E-mail
        $old_email          = $this->getOldEmail($db);
        $this->old_email    = $old_email;

        // Create E-mail Log
        $this->createLog($db);

        // Set New E-mail
        $this->setNewEmail($db);

        // User Timer
        $UserTimer->update($db);

        // Close Connection
        $db->close();

        // Header
        header('Location: https://www.sketchbook.cafe/settings/changeemail/');
        exit;
    }

    // Set New E-mail Address
    private function setNewEmail(&$db)
    {
        $method = 'ChangeEmailSubmit->setNewEmail()';

        // Set vars
        $user_id    = $this->user_id;
        $new_email  = $this->new_email;

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Update e-mail address for user
        $sql = 'UPDATE users
            SET email=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('si',$new_email,$user_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }

    // Create E-mail Log
    private function createLog(&$db)
    {
        $method = 'ChangeEmailSubmit->createLog()';

        // Set vars
        $user_id    = $this->user_id;
        $time       = $this->time;
        $ip_address = $this->ip_address;
        $old_email  = $this->old_email;
        $new_email  = $this->new_email;

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Create a new log
        $sql = 'INSERT INTO log_email_change
            SET user_id=?,
            ip_address=?,
            date_created=?,
            old_email=?,
            new_email=?';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('isiss',$user_id,$ip_address,$time,$old_email,$new_email);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }

    // Get Old E-mail
    private function getOldEmail(&$db)
    {
        $method = 'ChangeEmailSubmit->getOldEmail()';

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Set Vars
        $user_id = $this->user_id;
        if ($user_id < 1)
        {
            error('Dev error: $user_id is not set for ChangeEmailSubmit->getOldEmail()');
        }

        // Get email
        $sql = 'SELECT email
            FROM users
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$user_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Set Old E-mail
        $old_email = isset($row['email']) ? $row['email'] : '';

        // Return
        return $old_email;
    }
}