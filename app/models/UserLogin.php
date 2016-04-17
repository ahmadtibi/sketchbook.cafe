<?php
// User Login Stuff
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
        // Initialize Objects
        $db     = &$obj_array['db'];

        // Functions + Classes
        sbc_function('get_username');
        sbc_function('get_password');
        sbc_function('rd');
        sbc_class('UserSession');
        sbc_class('LoginTimer');
        sbc_class('IpTimer');

        // Initialize Vars
        $this->ip_address   = $_SERVER['REMOTE_ADDR'];
        $this->time         = time();
        $this->rd           = rd();
        $password_temp      = '';
        $session_id1        = 0;
        $session_id2        = 0;

        // Set vars
        $this->username     = get_username($_POST['username']);
        $password           = get_password($_POST['password']);
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
        $sql = 'SELECT id, password
            FROM users
            WHERE username=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('s',$username);
        if (!$stmt->execute())
        {
            error('Could not execute statement for UserLogin->construct()');
        }
        $result = $stmt->get_result();
        $row    = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        $stmt->close();

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