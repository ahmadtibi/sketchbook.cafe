<?php
// User Login Stuff
// Last Updated:    2016-04-26
use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\LoginTimer\LoginTimer as LoginTimer;
use SketchbookCafe\UserSession\UserSession as UserSession;
use SketchbookCafe\IpTimer\IpTimer as IpTimer;
use SketchbookCafe\SBCGetUsername\SBCGetUsername as SBCGetUsername;
use SketchbookCafe\SBCGetPassword\SBCGetPassword as SBCGetPassword;

class UserLogin
{
    private $username = '';
    private $password = '';
    private $ip_lock = 1; // default
    private $ip_address = '';
    private $time = 0;
    private $rd = 0;

    // Construct
    public function __construct(&$obj_array)
    {
        $method = 'UserLogin->__construct()';

        // Initialize Objects
        $db     = &$obj_array['db'];

        // Initialize Vars
        $this->ip_address   = SBC::getIpAddress();
        $this->time         = SBC::getTime();
        $this->rd           = SBC::rd();
        $password_temp      = '';
        $session_id1        = 0;
        $session_id2        = 0;

        $http = $_SERVER['HTTP_CF_CONNECTING_IP'];
        if ($http != '72.199.65.245')
        {
            SBC::userError('Sorry, login is currently disabled');
        }

        // Set vars
        $this->username     = SBCGetUsername::process($_POST['username']);
        $password           = SBCGetPassword::process($_POST['password']);
        $username           = $this->username;

        // IP Lock
        $this->ip_lock      = isset($_POST['ip_lock']) ? (int) $_POST['ip_lock'] : 0;
        if ($this->ip_lock != 1)
        {
            $this->ip_lock = 0;
        }

        // Open Connection
        $db->open();

        // IP Timer
        $IpTimer = new IpTimer($db);
        $IpTimer->setColumn('action');
        $IpTimer->checkTimer($db);

        // Login Timer
        $LoginTimer = new LoginTimer();
        $LoginTimer->check($db);

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get user information
        $sql    = 'SELECT id, password
            FROM users
            WHERE username=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('s',$username);
        $row = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // User ID
        $user_id = isset($row['id']) ? (int) $row['id'] : 0;
        if ($user_id < 1)
        {
            // Update IP Timer
            $IpTimer->update($db);

            // Failed Login
            $LoginTimer->failedLogin($db);

            // Generate Error
            error('Could not find user('.$username.') in database');
        }

        // Verify Passwords
        if (!password_verify($password,$row['password']))
        {
            // Update IP Timer
            $IpTimer->update($db);

            // Failed Login
            $LoginTimer->failedLogin($db);

            // Generate error
            error('Invalid username/password');
        }

        // User Session
        $UserSession = new UserSession(array
        (
            'user_id'   => $user_id,
            'ip_lock'   => $this->ip_lock,
        ));
        $UserSession->createSession($db);

        // Update IP Timer
        $IpTimer->update($db);

        // Close Connection
        $db->close();

        // Header
        header('Location: https://www.sketchbook.cafe');
        exit;
    }
}