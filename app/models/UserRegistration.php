<?php

class UserRegistration
{
    public $name = '';

    // User Input
    private $username = '';
    private $email = '';
    private $password = '';
    private $termsofservice = 0;
    private $ip_address = '';
    private $time = 0;
    private $rd = 0;

    // Settings
    private $username_free = 0;

    // Generated
    private $checkinfo = 0;
    private $user_id = 0;
    private $ip_timer_id = 0;

    // Construct
    public function __construct()
    {
        // Globals
        global $db;

        // Functions + Classes
        sbc_function('get_username');
        sbc_function('get_email');
        sbc_function('get_password');
        sbc_function('rd');
        sbc_class('IpTimer');
        sbc_class('UserSession');
        sbc_class('TableUser');

        // Recaptcha Settings
        require '../app/recaptcha_settings.php';

        // Address
        $this->ip_address = $_SERVER['REMOTE_ADDR'];

        // Random Digit and Time
        $this->rd = rd();
        $this->time = time();

        // Initialize Variables
        $pass1          = '';
        $pass2          = '';

        // Username
        $this->username = get_username($_POST['username']);

        // E-mail
        $this->email = get_email($_POST['email']);

        // Passwords
        $pass1 = get_password($_POST['pass1']);
        $pass2 = get_password($_POST['pass2']);
        if ($pass1 != $pass2)
        {
            error('Passwords do not match');
        }

        // Create a hashed password instead
        $this->password = password_hash($pass1,PASSWORD_DEFAULT);

        // Terms of Service + Privacy Policy
        $this->termsofservice = isset($_POST['termsofservice']) ? (int) $_POST['termsofservice'] : 0;
        if ($this->termsofservice != 1)
        {
            error('You must read and agree to the Terms of Service and Privacy Policy to register on this site.');
        }

        // Recaptcha
        $gRecaptchaResponse = $_POST['g-recaptcha-response'];
        $recaptcha          = new \ReCaptcha\ReCaptcha($recaptcha_settings['secret']);
        unset($recaptcha_settings);
        $resp = $recaptcha->verify($gRecaptchaResponse, $this->ip_address);
        if (!$resp->isSuccess()) {
            error('Invalid Recaptcha. Please try again.');
        }

        // Double check information
        $this->checkInfo();

        // Open Connection
        $db->open();

        // IP Timer
        $IpTimer = new IpTimer($db);
        $IpTimer->setColumn('register');

        // IP Timer: Registration
        $IpTimer->checkTimer($db);

        // Check if username exists
        $this->checkUsernameExists($db);

        // Create New User
        $this->createNewUser($db);

        // Create User Session and Login?
        $UserSession = new UserSession(array
        (
            'user_id'   => $this->user_id,
            'ip_lock'   => 1,
        ));
        $UserSession->createSession($db);

        // User Tables
        $TableUser = new TableUser($this->user_id);
        $TableUser->checkTables($db);

        // Update IP Timer
        $IpTimer->update($db);

        // Close Connection
        $db->close();

        // Back to the frontpage?
        header('Location: https://www.sketchbook.cafe');
        exit;
    }

    // Check Info - double check if everything is valid
    private function checkInfo()
    {
        // Double check
        if (empty($this->username) || empty($this->password))
        {
            error('Odd... something is missing...');
        }

        // Other vars
        if (empty($this->ip_address) || $this->rd < 1 || $this->time < 1)
        {
            error('Something is missing...: ' . $this->ip_address . ' / ' . $this->rd . ' / ' . $this->time);
        }
    }

    // Check if the user exists
    private function checkUsernameExists(&$db)
    {
        // Correct DB
        $db->sql_switch('sketchbookcafe');

        // Vars
        $username = $this->username;

        // Check if the username already exists
        $sql = 'SELECT id
            FROM users
            WHERE username=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('s',$username);
        if (!$stmt->execute())
        {
            error('Could not execute statement');
        }
        $result = $stmt->get_result();
        $row    = $db->sql_fetchrow($result);
        $stmt->close();

        // Check
        if ($row['id'] > 0)
        {
            error('A user already exists with this username');
        }

        // Set as free
        $this->username_free = 1;
    }

    // Create a New User
    private function createNewUser(&$db)
    {
        // Double check!
        if ($this->username_free != 1)
        {
            error('Something went wrong! Username is not free...');
        }

        // Set Variables
        $user_id    = 0; // initialize
        $username   = $this->username;
        $email      = $this->email;
        $password   = $this->password;
        $time       = $this->time;
        $ip_address = $this->ip_address;

        // Sql
        $sql = 'INSERT INTO users
            SET username=?, 
            email=?,
            password=?, 
            date_registered=?, 
            date_lastlogin=?, 
            ip_registered=?, 
            ip_lastlogin=?';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('sssiiss',$username,$email,$password,$time,$time,$ip_address,$ip_address);
        if (!$stmt->execute())
        {
            error('Could not insert new user into database');
        }
        $stmt->close();

        // Get User ID
        $sql = 'SELECT id
            FROM users
            WHERE username=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('s',$username);
        if (!$stmt->execute())
        {
            error('Could not get ID from new user...');
        }
        $result = $stmt->get_result();
        $row    = $db->sql_fetchrow($result);
        $stmt->close();

        // User ID?
        $this->user_id = $row['id'];
        if ($this->user_id < 1)
        {
            error('Could not get new user_id from database. Please contact an administrator');
        }
    }
}