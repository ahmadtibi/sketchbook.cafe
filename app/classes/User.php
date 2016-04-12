<?php
/**
*
* User Class
*
* @author       Jonathan Maltezo (Kameloh)
* @copyright    (c) 2016, Jonathan Maltezo (Kameloh)
* @lastupdated  2016-04-12
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

        // Do we have valid credentials?
        if ($id > 0 && $rd > 0 && $session_id > 0 && !empty($session_code))
        {
            // Switch
            $db->sql_switch('sketchbookcafe');

            // Get user information
            $sql = 'SELECT id, session_id1, session_id2
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
        $sql = 'SELECT id, username, timezone_my, avatar_id, avatar_url 
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
}