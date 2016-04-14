<?php
/**
*
* User Class
*
* @author       Jonathan Maltezo (Kameloh)
* @copyright    (c) 2016, Jonathan Maltezo (Kameloh)
* @lastupdated  2016-04-14
*
*/
// Main user class
class User
{
    private $id = 0;
    private $rd = 0;
    private $session_id = 0;
    private $session_code = '';
    private $ip_address = '';

    // Settings
    public $username = 'Guest';
    public $avatar_id = 0;
    public $avatar_url = '';
    private $auth_type = 0; // 1 optional, 2 required
    private $timezone_my = 'America/Los_Angeles';
    private $timezone_id = 6;

    // Generated Variables
    private $dtzone = '';
    private $loggedin = 0;

    // Db
    private $data;

    // Admin Variables
    private $isadmin = 0; // reserved for global administrators
    private $admin_session1 = '';
    private $admin_session2 = '';
    private $admin_session3 = '';

    // Construct
    public function __construct()
    {
        // Functions
        sbc_function('get_session_code');

        // Get Cookies
        $id             = isset($_COOKIE['id']) ? (int) $_COOKIE['id'] : 0;
        $rd             = isset($_COOKIE['rd']) ? (int) $_COOKIE['rd'] : 0;
        $session_id     = isset($_COOKIE['session_id']) ? (int) $_COOKIE['session_id'] : 0;
        $session_code   = isset($_COOKIE['session_code']) ? get_session_code($_COOKIE['session_code']) : '';

        // Other Vars
        $ip_address     = $_SERVER['REMOTE_ADDR'];

        // Checks
        if ($id < 1)
        {
            $id = 0;
        }
        if ($rd < 1)
        {
            $rd = 0;
        }
        if ($session_id < 1)
        {
            $session_id = 0;
        }

        // Set vars
        $this->id           = $id;
        $this->rd           = $rd;
        $this->session_id   = $session_id;
        $this->session_code = $session_code;
        $this->ip_address   = $ip_address;
    }

    // Get User ID
    final public function getUserId()
    {
        return $this->id;
    }

    // Logged In
    final public function loggedIn()
    {
        if ($this->id > 0)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    // Log out!
    final public function logout()
    {
        // Global
        global $db;

        // Open Connection
        $db->open();

        // Initialize Vars
        $ip_address     = $_SERVER['REMOTE_ADDR'];
        $time           = time();
        $id             = $this->id;
        $rd             = $this->rd;
        $session_id     = $this->session_id;
        $session_code   = $this->session_code;
        $isadmin        = 0;

        // Set Vars
        $cookie_path    = '/';
        $cookie_domain  = '.sketchbook.cafe';
        $cookie_life    = 5184000;
        $cookie_time    = $time + $cookie_life;
        $https          = true;
        $http_only      = true;

        // Remove Cookies
        setcookie('id','',$cookie_time,$cookie_path,$cookie_domain,$https,$http_only);
        setcookie('rd','',$cookie_time,$cookie_path,$cookie_domain,$https,$http_only);
        setcookie('session_id','',$cookie_time,$cookie_path,$cookie_domain,$https,$http_only);
        setcookie('session_code','',$cookie_time,$cookie_path,$cookie_domain,$https,$http_only);

        // Remove Administrator Cookies
        setcookie('admin_session1','',$cookie_time,$cookie_path,$cookie_domain,$https,$http_only);
        setcookie('admin_session2','',$cookie_time,$cookie_path,$cookie_domain,$https,$http_only);
        setcookie('admin_session3','',$cookie_time,$cookie_path,$cookie_domain,$https,$http_only);

        // Do we have valid credentials?
        if ($id > 0 && $rd > 0 && $session_id > 0 && !empty($session_code))
        {
            // Switch
            $db->sql_switch('sketchbookcafe');

            // Get user information
            $sql = 'SELECT id, isadmin, session_id1, session_id2
                FROM users
                WHERE id=?
                LIMIT 1';
            $stmt = $db->prepare($sql);
            $stmt->bind_param('i',$id);
            if (!$stmt->execute())
            {
                error('Could not execute statement for user->logout()');
            }
            $result = $stmt->get_result();
            $row    = $db->sql_fetchrow($result);
            $stmt->close();

            // Initialize
            $replace    = 0;
            $column_id  = 0;
            $user_id    = isset($row['id']) ? $row['id'] : 0;

            // Valid user?
            if ($user_id > 0)
            {
                // Check isadmin
                $isadmin = $row['isadmin'];

                // Which to replace?
                if ($row['session_id1'] == $session_id)
                {
                    $replace = 1;
                }
                else
                {
                    $replace = 2;
                }
            }

            // Replace?
            if ($replace > 0)
            {
                // Get session information
                $sql = 'SELECT id, user_id, session_code, ip_address, ip_lock
                    FROM login_sessions
                    WHERE id=?
                    LIMIT 1';
                $stmt = $db->prepare($sql);
                $stmt->bind_param('i',$session_id);
                if (!$stmt->execute())
                {
                    error('Could not execute statement (get session information) for User->logout()');
                }
                $result = $stmt->get_result();
                $row    = $db->sql_fetchrow($result);
                $stmt->close();

                // Validate
                $temp_id = isset($row['id']) ? $row['id'] : 0;
                if ($temp_id > 0)
                {
                    // Check
                    if ($row['user_id'] == $id && $row['session_code'] == $session_code)
                    {
                        // Initialize
                        $is_valid = 0;

                        // IP Lock?
                        if ($row['ip_lock'] == 1)
                        {
                            // IP Check
                            if ($row['ip_address'] == $ip_address)
                            {
                                $is_valid = 1;
                            }
                        }
                        else
                        {
                            // Set as valid since session code is correct
                            $is_valid = 1;
                        }

                        // Valid?
                        if ($is_valid == 1)
                        {
                            // Delete session
                            $sql = 'DELETE FROM login_sessions
                                WHERE id=?
                                LIMIT 1';
                            $stmt = $db->prepare($sql);
                            $stmt->bind_param('i',$session_id);
                            if (!$stmt->execute())
                            {
                                error('Could not execute statement (delete login session) in User->logout()');
                            }
                            $stmt->close();

                            // Update User
                            $sql = 'UPDATE users
                                SET session_id'.$replace.'=0
                                WHERE id=?
                                LIMIT 1';
                            $stmt = $db->prepare($sql);
                            $stmt->bind_param('i',$id);
                            if (!$stmt->execute())
                            {
                                error('Could not execute statement (update user) in User->logout()');
                            }
                            $stmt->close();

                            // Administrator Cookies. Remove them since this session is valid.
                            if ($isadmin == 1)
                            {
                                // Update Admin
                                $sql = 'UPDATE admins
                                    SET session_active=0
                                    WHERE user_id=?
                                    LIMIT 1';
                                $stmt = $db->prepare($sql);
                                $stmt->bind_param('i',$id);
                                if (!$stmt->execute())
                                {
                                    error('Could not execute statement (update admin) for User->logout()');
                                }
                                $stmt->close();
                            }
                        }
                    }
                }
            }
        }

        // Close DB
        $db->close();

        // Header
        header('Location: https://www.sketchbook.cafe');
        exit;
    }

    // Date Time Zone
    final private function dtzone()
    {
        $this->dtzone = new DateTimeZone($this->timezone_my);
    }

    // Authenticate
    final private function auth(&$db)
    {
        // Set Vars
        $id                 = $this->id;
        $ip_address         = $this->ip_address;
        $error_message      = 'Invalid session. <a href="https://www.sketchbook.cafe/logout/">Please logout</a> and try again.';
        $error_logged_in    = 'You must be <a href="https://www.sketchbook.cafe/">logged in</a> to view this page.';

        // Authentication Type
        if ($this->auth_type < 1 || $this->auth_type > 2)
        {
            error('Dev error: $auth_type is not correctly set for User->auth() : '.$this->auth_type);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // 2: Required
        if ($this->auth_type == 2)
        {
            // Set vars
            $session_id     = $this->session_id;
            $session_code   = $this->session_code;

            // Check Session
            if ($session_id < 1 || empty($session_code))
            {
                error($error_message);
            }

            // Get Session Information
            $sql = 'SELECT id, user_id, session_code, ip_address, ip_lock
                FROM login_sessions
                WHERE id=?
                LIMIT 1';
            $stmt = $db->prepare($sql);
            $stmt->bind_param('i',$session_id);
            if (!$stmt->execute())
            {
                error('Could not execute statement (get session info) for User->auth()');
            }
            $result = $stmt->get_result();
            $row    = $db->sql_fetchrow($result);
            $db->sql_freeresult($result);
            $stmt->close();

            // Check if Session Exists || IDs Match || Sessions Match
            if ($row['id'] < 1 || $row['user_id'] != $this->id || $row['session_code'] != $session_code)
            {
                error($error_message);
            }

            // IP Lock
            if ($row['ip_lock'] == 1)
            {
                // Check
                if ($row['ip_address'] != $ip_address)
                {
                    error($error_message);
                }
            }
        }

        // Get User Information
        $sql = 'SELECT id, username, isadmin, timezone_my, timezone_id, avatar_id, avatar_url 
            FROM users
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$id);
        if (!$stmt->execute())
        {
            error('Could not execute statement (get user information) for User->Auth()');
        }
        $result = $stmt->get_result();
        $row    = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        $stmt->close();

        // Do we have a user?
        if ($row['id'] > 0)
        {
            // Set vars
            $this->isadmin      = $row['isadmin'];
            $this->username     = $row['username'];
            $this->data         = $row;
            $this->timezone_my  = $row['timezone_my'];
            $this->avatar_id    = $row['avatar_id'];
            $this->avatar_url   = $row['avatar_url'];
        }
        else
        {
            // We have to do something...
            if ($this->auth_type == 2)
            {
                error('Odd.. cannot find user in database.');
            }
        }
    }

    // Required User
    final public function required(&$db)
    {
        // User ID
        if ($this->id < 1)
        {
            error('You must be logged in to view this page');
        }

        // Set Auth Type (1 optional, 2 required);
        $this->auth_type = 2;

        // Auth
        $this->auth($db);

        // Generate Dtzone
        $this->dtzone();
    }

    // Optional User
    final public function optional(&$db)
    {
        // If user ID is set or not
        if ($this->id < 1)
        {
            return null;
        }

        // Auth settings (1 is optional);
        $this->auth_type = 1;

        // Auth
        $this->auth($db);

        // Generate dtzone
        $this->dtzone();
    }

    // Is Admin?
    final public function isAdmin()
    {
        if ($this->isadmin == 1)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    // Get Column
    final public function getColumn($input)
    {
        return $this->data[$input];
    }

    // Password Match Check
    final public function checkPasswordMatch(&$db,$password)
    {
        // Double check!
        if (empty($password))
        {
            error('Dev error: $password is not set for User->checkPasswordMatch()');
        }

        // Set vars
        $user_id = $this->id;
        if ($user_id < 1)
        {
            error('Dev error: $user_id is not set for User->checkPasswordMatch()');
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get user password to check
        $sql = 'SELECT password
            FROM users
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$user_id);
        if (!$stmt->execute())
        {
            error('Could not execute statement (get user info for password) in User->checkPasswordMatch()');
        }
        $result = $stmt->get_result();
        $row    = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        $stmt->close();

        // Verify Passwords
        if (!password_verify($password,$row['password']))
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    // Admin Only
    final public function admin(&$db)
    {
        // Required Users
        $this->required($db);

        // Is admin?
        if ($this->isadmin != 1)
        {
            error('Sorry, only administrators may access this area.');
        }

        // Authenticate Admin
        $this->adminCheckAuth($db);

        // Generate Dtzone
        $this->dtzone();
    }

    // Admin Check Admin
    final public function adminCheckAuth(&$db)
    {
        // Classes + Functions
        sbc_function('get_session_code');
        sbc_class('LoginTimer');

        // Login Timer
        $LoginTimer = new LoginTimer();
        $LoginTimer->check($db);

        // Error Messages
        $error_invalid = 'Invalid admin session. <a href="https://www.sketchbook.cafe/logout/">Please logout</a> and try again.';

        // Initialize Vars
        $ip_address = $this->ip_address;

        // User ID
        $user_id = $this->id;
        if ($user_id < 1)
        {
            error('Dev error: $user_id is not set for User->adminCheckAuth()');
        }

        // Make sure they're an admin
        if ($this->isadmin != 1)
        {
            error('Sorry, only administrators my access this area.');
        }

        // Get Administrator Cookies
        $admin_session1 = isset($_COOKIE['admin_session1']) ? get_session_code($_COOKIE['admin_session1']) : '';
        $admin_session2 = isset($_COOKIE['admin_session2']) ? get_session_code($_COOKIE['admin_session2']) : '';
        $admin_session3 = isset($_COOKIE['admin_session3']) ? get_session_code($_COOKIE['admin_session3']) : '';

        // Has Cookies
        $has_cookies = 0;
        if (!empty($admin_session1) && !empty($admin_session2) && !empty($admin_session3))
        {
            $has_cookies = 1;
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Admin Info
        $sql = 'SELECT id, haspass, ip_address, session_active, admin_session1, admin_session2, admin_session3 
            FROM admins
            WHERE user_id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$user_id);
        if (!$stmt->execute())
        {
            error('Could not execute statement (get admin info) for User->adminCheckAuth()');
        }
        $result = $stmt->get_result();
        $row    = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        $stmt->close();

        // Admin in database?
        $admin_found = isset($row['id']) ? (int) $row['id'] : 0;
        if ($admin_found < 1)
        {
            error('Dev error: could not find administrator in database');
        }

        // Does the administrator have an admin password set up?
        $haspass = isset($row['haspass']) ? (int) $row['haspass'] : 0;
        if ($haspass != 1)
        {
            error('Admin notice: Administrator Password not set. <a href="https://www.sketchbook.cafe/settings/adminsetup/">Click here</a> to setup.');
        }

        // Is there an active session?
        if ($row['session_active'] != 1 || $has_cookies != 1)
        {
            error('Please login here: <a href="https://www.sketchbook.cafe/adminlogin/">Login Page</a>');
        }

        // Verify IP Address and Sessions
        if ($row['ip_address'] != $ip_address
            || $admin_session1 != $row['admin_session1'] 
            || $admin_session2 != $row['admin_session2'] 
            || $admin_session3 != $row['admin_session3'])
        {
            // Failed Login
            $LoginTimer->failedLogin($db);

            // Generate Error
            error($error_invalid);
        }
    }
}