<?php
// @author          Kameloh
// @lastUpdated     2016-04-27

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\LoginTimer\LoginTimer as LoginTimer;
use SketchbookCafe\SBCGetPassword\SBCGetPassword as SBCGetPassword;
use SketchbookCafe\GenerateRandom\GenerateRandom as GenerateRandom;

class AdminLoginSubmit
{
    private $user_id = 0;
    private $admin_id = 0;
    private $ip_address = '';
    private $time = 0;
    private $admin_session1 = '';
    private $admin_session2 = '';
    private $admin_session3 = '';
    private $hasinfo = 0;

    // User Input
    private $pass1 = '';
    private $pass2 = '';
    private $pass3 = '';

    private $obj_array = [];

    // Construct
    public function __construct(&$obj_array)
    {
        $method = 'AdminLoginSubmit->__construct()';

        // Initialize Objects
        $db                 = &$obj_array['db'];
        $User               = &$obj_array['User'];
        $this->obj_array    = &$obj_array;

        // Initialize Vars
        $this->ip_address   = SBC::getIpAddress();
        $this->time         = SBC::getTime();

        // Get Passwords
        $this->pass1        = SBCGetPassword::process($_POST['pass1']);
        $this->pass2        = SBCGetPassword::process($_POST['pass2']);
        $this->pass3        = SBCGetPassword::process($_POST['pass3']);

        // Generate Admin Sessions
        $this->admin_session1   = GenerateRandom::process(250);
        $this->admin_session2   = GenerateRandom::process(250);
        $this->admin_session3   = GenerateRandom::process(250);

        // Open
        $db->open();

        // User Required
        $User->required($db);
        $user_id        = $User->getUserId();
        $this->user_id  = $user_id;
        if (!$User->isAdmin())
        {
            SBC::userError('Sorry, only administrators may access this area.');
        }

        // Login Timer
        $LoginTimer = new LoginTimer();
        $LoginTimer->check($db);

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Admin Information
        $sql = 'SELECT id, password1, password2, password3
            FROM admins
            WHERE user_id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$user_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Admin ID
        $admin_id = isset($row['id']) ? (int) $row['id'] : 0;
        if ($admin_id < 1)
        {
            // Failed Logins
            $LoginTimer->failedLogin($db);

            // Generate Error
            SBC::userError('Could not find admin in database.');
        }

        // Set vars
        $this->admin_id = $admin_id;

        // Verify Passwords
        $this->adminVerifyPassword($this->pass1,$row['password1'],$LoginTimer);
        $this->adminVerifyPassword($this->pass2,$row['password2'],$LoginTimer);
        $this->adminVerifyPassword($this->pass3,$row['password3'],$LoginTimer);
    
        // Set as has info!
        $this->hasinfo = 1;

        // Update Admin's Session
        $this->createNewSession($db);

        // Set Cookies
        $this->setCookies($db);

        // Admin Login Log
        $this->logAdmin($db);

        // Close
        $db->close();

        // Header
        header('Location: https://www.sketchbook.cafe/admin/');
        exit;
    }

    // Admin Login Log
    private function logAdmin(&$db)
    {
        $method = 'AdminLoginSubmit->logAdmin()';

        // Has info?
        $this->hasinfo();

        // Set vars
        $user_id    = $this->user_id;
        $ip_address = $this->ip_address;
        $time       = $this->time;

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Create a new log for an admin login
        $sql = 'INSERT INTO log_admin_login
            SET user_id=?, 
            ip_address=?, 
            date_created=?';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('isi',$user_id,$ip_address,$time);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }

    // Set Cookies
    private function setCookies(&$db)
    {
        $method = 'AdminLoginSubmit->setCookies()';

        // Has info?
        $this->hasinfo();

        // Initialize Vars
        $admin_session1 = $this->admin_session1;
        $admin_session2 = $this->admin_session2;
        $admin_session3 = $this->admin_session3;

        // Cookie Information
        $time           = $this->time;
        $cookie_path    = '/';
        $cookie_domain  = '.sketchbook.cafe';
        $cookie_life    = 5184000;
        $cookie_time    = $time + $cookie_life;
        $https          = true;
        $http_only      = true;

        // Set Cookies
        setcookie('admin_session1',$admin_session1,$cookie_time,$cookie_path,$cookie_domain,$https,$http_only);
        setcookie('admin_session2',$admin_session2,$cookie_time,$cookie_path,$cookie_domain,$https,$http_only);
        setcookie('admin_session3',$admin_session3,$cookie_time,$cookie_path,$cookie_domain,$https,$http_only);
    }

    // Update Admin's Session
    private function createNewSession(&$db)
    {
        $method = 'AdminLoginSubmit->createNewSession()';

        // Do we have info?
        $this->hasInfo();

        // Initialize Vars
        $user_id        = $this->user_id;
        $admin_id       = $this->admin_id;
        $ip_address     = $this->ip_address;
        $time           = $this->time;
        $admin_session1 = $this->admin_session1;
        $admin_session2 = $this->admin_session2;
        $admin_session3 = $this->admin_session3;

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Update administrator
        $sql = 'UPDATE admins
            SET ip_address=?,
            session_active=1,
            admin_session1=?,
            admin_session2=?,
            admin_session3=?
            WHERE id=?
            AND user_id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ssssii',$ip_address,$admin_session1,$admin_session2,$admin_session3,$admin_id,$user_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }

    // Has info
    private function hasInfo()
    {
        $method = 'AdminLoginSubmit->hasInfo()';

        if ($this->hasinfo != 1)
        {
            SBC::devError('$hasinfo is not set',$method);
        }
    }

    // Admin Verify Password
    final private function adminVerifyPassword($password,$admin_password,&$LoginTimer)
    {
        $method = 'AdminLoginSubmit->adminVerifyPassword()';

        // Set Object
        $db = &$this->obj_array['db'];

        // Double check
        if (empty($password) || empty($admin_password))
        {
            SBC::devError('$password or $admin_password is empty',$method);
        }

        // Verify 
        if (!password_verify($password,$admin_password))
        {
            // Failed Logins
            $LoginTimer->failedLogin($db);

            // Generate Error
            SBC::userError('Invalid admin password.');
        }
    }
}