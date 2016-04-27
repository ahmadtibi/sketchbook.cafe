<?php
/**
*
* User Class
*
* @author       Jonathan Maltezo (Kameloh)
* @copyright    (c) 2016, Jonathan Maltezo (Kameloh)
* @lastupdated  2016-04-27
*
*/
// Main user class
namespace SketchbookCafe\User;

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\LoginTimer\LoginTimer as LoginTimer;

class User
{
    private $id = 0;
    private $rd = 0;
    private $session_id = 0;
    private $session_code = '';
    private $ip_address = '';
    private $frontpage = 0;

    // Settings
    public $username = 'Guest';
    public $avatar_id = 0;
    public $avatar_url = '';
    private $auth_type = 0; // 1 optional, 2 required
    private $timezone_my = 'America/Los_Angeles';
    private $timezone_id = 6;
    public $mailbox_update = 0;
    public $mailbox_lastupdate = 0;
    public $mail_total = 0;

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
    private $admin_flag = [];

    // Construct
    public function __construct()
    {
        $method = 'User->__construct()';

        // Get Cookies
        $id             = isset($_COOKIE['id']) ? (int) $_COOKIE['id'] : 0;
        $rd             = isset($_COOKIE['rd']) ? (int) $_COOKIE['rd'] : 0;
        $session_id     = isset($_COOKIE['session_id']) ? (int) $_COOKIE['session_id'] : 0;
        $session_code   = isset($_COOKIE['session_code']) ? SBC::getSessionCode($_COOKIE['session_code']) : '';

        // Other Vars
        $ip_address     = SBC::getIpAddress();

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
        $method = 'User->getUserId()';
        return $this->id;
    }

    // Logged In
    final public function loggedIn()
    {
        $method = 'User->loggedIn()';
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
    final public function logout(&$db)
    {
        $method = 'User->logout()';

        // Open Connection
        $db->open();

        // Initialize Vars
        $ip_address     = SBC::getIpAddress();
        $time           = SBC::getTime();
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
            $stmt   = $db->prepare($sql);
            $stmt->bind_param('i',$id);
            $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

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
                $stmt   = $db->prepare($sql);
                $stmt->bind_param('i',$session_id);
                $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

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
                            SBC::statementExecute($stmt,$db,$sql,$method);

                            // Update User
                            $sql = 'UPDATE users
                                SET session_id'.$replace.'=0
                                WHERE id=?
                                LIMIT 1';
                            $stmt = $db->prepare($sql);
                            $stmt->bind_param('i',$id);
                            SBC::statementExecute($stmt,$db,$sql,$method);

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
                                SBC::statementExecute($stmt,$db,$sql,$method);
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
        $method = 'User->dtzone()';

        $this->dtzone = new \DateTimeZone($this->timezone_my);
    }

    // Authenticate
    final private function auth(&$db)
    {
        $method = 'User->auth()';

        // Set Vars
        $id                 = $this->id;
        $ip_address         = $this->ip_address;
        $error_message      = 'Invalid session. <a href="https://www.sketchbook.cafe/logout/">Please logout</a> and try again.';
        $error_logged_in    = 'You must be <a href="https://www.sketchbook.cafe/">logged in</a> to view this page.';

        // Authentication Type
        if ($this->auth_type < 1 || $this->auth_type > 2)
        {
            SBC::devError('$auth_type is not correctly set',$method);
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
            $stmt   = $db->prepare($sql);
            $stmt->bind_param('i',$session_id);
            $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

            // Check if Session Exists || IDs Match || Sessions Match
            if ($row['id'] < 1 || $row['user_id'] != $this->id || $row['session_code'] != $session_code)
            {
                SBC::userError($error_message);
            }

            // IP Lock
            if ($row['ip_lock'] == 1)
            {
                // Check
                if ($row['ip_address'] != $ip_address)
                {
                    SBC::userError($error_message);
                }
            }
        }

        // Get User Information
        $sql = 'SELECT id, username, isadmin, timezone_my, timezone_id, avatar_id, avatar_url, 
            mail_total, mailbox_update, mailbox_lastupdate 
            FROM users
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

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
            $this->mail_total   = $row['mail_total'];

            // Only calculate message centers from the frontpage
            if ($this->frontpage == 1)
            {
                // Check Mail
                $this->checkMail($db);
            }
        }
        else
        {
            // We have to do something...
            if ($this->auth_type == 2)
            {
                SBC::userError('Odd.. cannot find user in database.');
            }
        }
    }

    // Check Mail
    final private function checkMail(&$db)
    {
        $method = 'User->checkMail()';

        // Initialize Vars
        $user_id            = $this->id;
        $time               = SBC::getTime();
        $mailbox_update     = isset($this->data['mailbox_update']) ? $this->data['mailbox_update'] : 0;
        $mailbox_lastupdate = isset($this->data['mailbox_lastupdate']) ? $this->data['mailbox_lastupdate'] : 0;

        // Check
        if ($user_id < 1)
        {
            return null;
        }
        if ($mailbox_update < 1)
        {
            $mailbox_update = 0;
        }
        if ($mailbox_lastupdate < 1)
        {
            $mailbox_lastupdate = 0;
        }

        // Should we do a check?
        if ($mailbox_update > $mailbox_lastupdate)
        {
            // Switch
            $db->sql_switch('sketchbookcafe_users');

            // Table name
            $table_name = 'u'.$user_id.'m';

            // Count New
            $sql = 'SELECT COUNT(*) 
                FROM '.$table_name.'
                WHERE isnew=1';
            $result = $db->sql_query($sql);
            $row    = $db->sql_fetchrow($result);
            $db->sql_freeresult($result);

            // Set Total
            $total = isset($row[0]) ? (int) $row[0] : 0;
            if ($total < 1)
            {
                $total = 0;
            }

            // Switch
            $db->sql_switch('sketchbookcafe');

            // Update User
            $sql = 'UPDATE users
                SET mail_total=?,
                mailbox_lastupdate=?
                WHERE id=?
                LIMIT 1';
            $stmt = $db->prepare($sql);
            $stmt->bind_param('iii',$total,$time,$user_id);
            SBC::statementExecute($stmt,$db,$sql,$method);

            // Update Vars
            $this->mail_total   = $total;
        }
    }

    // Required User
    final public function required(&$db)
    {
        $method = 'User->required()';

        // User ID
        if ($this->id < 1)
        {
            SBC::userError('You must be logged in to view this page');
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
        $method = 'User->optional()';

        // If user ID is set or not
        if ($this->id < 1)
        {
            // Generate dtzone
            $this->dtzone();

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
        $method = 'User->isAdmin()';

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
        $method = 'User->getColumn()';
        return $this->data[$input];
    }

    // Password Match Check
    final public function checkPasswordMatch(&$db,$password)
    {
        $method = 'User->checkPasswordMatch()';

        // Double check!
        if (empty($password))
        {
            SBC::devError('$password is not set',$method);
        }

        // Set vars
        $user_id = $this->id;
        if ($user_id < 1)
        {
            SBC::devError('$user_id is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get user password to check
        $sql = 'SELECT password
            FROM users
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$user_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

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
        $method = 'User->admin()';

        // Required Users
        $this->required($db);

        // Is admin?
        if ($this->isadmin != 1)
        {
            SBC::userError('Sorry, only administrators may access this area.');
        }

        // Authenticate Admin
        $this->adminCheckAuth($db);

        // Generate Dtzone
        $this->dtzone();
    }

    // Admin Check Admin
    final public function adminCheckAuth(&$db)
    {
        $method = 'User->adminCheckAuth()';

        // Login Timer
        $LoginTimer = new LoginTimer();
        $LoginTimer->check($db);

        // Error Messages
        $error_invalid = 'Invalid admin session. <a href="https://www.sketchbook.cafe/logout/">Please logout</a> and try again.';

        // Initialize Vars
        $ip_address = $this->ip_address;
        $user_id    = $this->id;

        // Make sure User ID is set
        if ($user_id < 1)
        {
            SBC::devError('$user_id is not set',$method);
        }

        // Make sure they're an admin
        if ($this->isadmin != 1)
        {
            SBC::userError('Sorry, only administrators my access this area.');
        }

        // Get Administrator Cookies
        $admin_session1 = isset($_COOKIE['admin_session1']) ? SBC::getSessionCode($_COOKIE['admin_session1']) : '';
        $admin_session2 = isset($_COOKIE['admin_session2']) ? SBC::getSessionCode($_COOKIE['admin_session2']) : '';
        $admin_session3 = isset($_COOKIE['admin_session3']) ? SBC::getSessionCode($_COOKIE['admin_session3']) : '';

        // Has Cookies
        $has_cookies = 0;
        if (!empty($admin_session1) && !empty($admin_session2) && !empty($admin_session3))
        {
            $has_cookies = 1;
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Admin Info
        $sql = 'SELECT id, haspass, ip_address, session_active, admin_session1, admin_session2, admin_session3,
            manage_forum_categories, manage_forum_forums, fix_user_table, fix_forum_table, manage_forum,
            manage_forum_admins
            FROM admins
            WHERE user_id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$user_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Admin in database?
        $admin_found = isset($row['id']) ? (int) $row['id'] : 0;
        if ($admin_found < 1)
        {
            SBC::devError('could not find administrator in database',$method);
        }

        // Does the administrator have an admin password set up?
        $haspass = isset($row['haspass']) ? (int) $row['haspass'] : 0;
        if ($haspass != 1)
        {
            SBC::userError('Admin notice: Administrator Password not set. <a href="https://www.sketchbook.cafe/settings/adminsetup/">Click here</a> to setup.');
        }

        // Is there an active session?
        if ($row['session_active'] != 1 || $has_cookies != 1)
        {
            SBC::userError('Please login here: <a href="https://www.sketchbook.cafe/adminlogin/">Login Page</a>');
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
            SBC::userError($error_invalid);
        }

        // Set Admin Flags
        $this->admin_flag['manage_forum_categories']    = $row['manage_forum_categories'];
        $this->admin_flag['manage_forum_forums']        = $row['manage_forum_forums'];
        $this->admin_flag['fix_user_table']             = $row['fix_user_table'];
        $this->admin_flag['manage_forum']               = $row['manage_forum'];
        $this->admin_flag['fix_forum_table']            = $row['fix_forum_table'];
        $this->admin_flag['manage_forum_admins']        = $row['manage_forum_admins'];
    }

    // Require Admin Flag
    final public function requireAdminFlag($flag)
    {
        $method = 'User->requireAdminFlag()';

        // Make sure a flag is set
        $flag   = isset($flag) ? $flag : '';
        if (empty($flag))
        {
            SBC::devError('$flag is not set',$method);
        }

        // Flag must be set
        if ($this->admin_flag[$flag] != 1)
        {
            SBC::userError('Sorry, you do not have the necessary flags to access this area');
        }
    }

    // Has Admin Flag
    final public function hasAdminFlag($flag)
    {
        $method = 'User->hasAdminFlag()';
        if (isset($this->admin_flag[$flag]))
        {
            if ($this->admin_flag[$flag] == 1)
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }

    // My Timzone (mytz) - for displaying time
    final public function mytz($time,$format)
    {
        $method = 'User->mytz()';

        // Set dates
        $u_time     = date('r',$time);
        $d_time     = new \DateTime($u_time);
        $d_time->setTimeZone($this->dtzone);
        $n_time     = $d_time->format($format);

        // Return
        return $n_time;
    }

    // Force an update for mailbox
    final public function forceMailboxUpdate(&$db)
    {
        $method = 'User->forceMailboxUpdate()';

        // Initialize Vars
        $time       = SBC::getTime();
        $user_id    = $this->id;
        if ($user_id < 1)
        {
            return null;
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Update user
        $sql = 'UPDATE users
            SET mailbox_update=?
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('ii',$time,$user_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }

    // Set Frontpage
    final public function setFrontpage()
    {
        $this->frontpage = 1;
    }
}