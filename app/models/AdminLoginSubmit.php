<?php

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

    // Construct
    public function __construct()
    {
        // Clases + Functions
        sbc_class('LoginTimer');
        sbc_function('get_password');
        sbc_function('generate_random');

        // Globals
        global $db,$User;

        // Initialize Vars
        $this->ip_address   = $_SERVER['REMOTE_ADDR'];
        $this->time         = time();

        // Get Passwords
        $this->pass1        = get_password($_POST['pass1']);
        $this->pass2        = get_password($_POST['pass2']);
        $this->pass3        = get_password($_POST['pass3']);

        // Generate Admin Sessions
        $this->admin_session1   = generate_random(250);
        $this->admin_session2   = generate_random(250);
        $this->admin_session3   = generate_random(250);

        // Open
        $db->open();

        // User Required
        $User->required($db);
        $user_id        = $User->getUserId();
        $this->user_id  = $user_id;
        if (!$User->isAdmin())
        {
            error('Sorry, only administrators may access this area.');
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
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$user_id);
        if (!$stmt->execute())
        {
            error('Could not execute statement (get admin information) for AdminLoginSubmit->construct()');
        }
        $result = $stmt->get_result();
        $row    = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        $stmt->close();

        // Admin ID
        $admin_id = isset($row['id']) ? (int) $row['id'] : 0;
        if ($admin_id < 1)
        {
            // Failed Logins
            $LoginTimer->failedLogin($db);

            // Generate Error
            error('Could not find admin in database.');
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
        if (!$stmt->execute())
        {
            error('Could not execute statement (create new log) for AdminLoginSubmit->logAdmin()');
        }
        $stmt->close();
    }

    // Set Cookies
    private function setCookies(&$db)
    {
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
        if (!$stmt->execute())
        {
            error('Could not execute statement (update administrator) for AdminLoginSumit->createNewSession()');
        }
        $stmt->close();
    }

    // Has info
    private function hasInfo()
    {
        if ($this->hasinfo != 1)
        {
            error('Dev error: $hasinfo is not set for AdminLoginSubmit->hasInfo()');
        }
    }

    // Admin Verify Password
    final private function adminVerifyPassword($password,$admin_password,&$LoginTimer)
    {
        // Globals
        global $db;

        // Double check
        if (empty($password) || empty($admin_password))
        {
            error('Dev error: $password or $admin_password is empty for function admin_verify_password()');
        }

        // Verify 
        if (!password_verify($password,$admin_password))
        {
            // Failed Logins
            $LoginTimer->failedLogin($db);

            // Generate Error
            error('Invalid admin password.');
        }
    }
}