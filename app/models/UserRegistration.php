<?php
// @author          Jonathan Maltezo (Kameloh)
// @lastUpdated     2016-04-27

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\SBCGetUsername\SBCGetUsername as SBCGetUsername;
use SketchbookCafe\SBCGetPassword\SBCGetPassword as SBCGetPassword;
use SketchbookCafe\SBCGetEmail\SBCGetEmail as SBCGetEmail;
use SketchbookCafe\IpTimer\IpTimer as IpTimer;
use SketchbookCafe\TableUser\TableUser as TableUser;
use SketchbookCafe\UserSession\UserSession as UserSession;

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
    public function __construct(&$obj_array)
    {
        $method = 'UserRegistration->__construct()';

        // Initialize Objects
        $db     = &$obj_array['db'];

        // Recaptcha Settings
        require '../app/recaptcha_settings.php';

        // Address
        $this->ip_address = SBC::getIpAddress();

        // Registration Closed (temp)
        SBC::userError('Sorry, registration is currently closed');

        // Random Digit and Time
        $this->rd   = SBC::rd();
        $this->time = SBC::getTime();

        // Initialize Variables
        $pass1          = '';
        $pass2          = '';

        // Username
        $this->username = SBCGetUsername::process($_POST['username']);

        // E-mail
        $this->email    = SBCGetEmail::process($_POST['email']);

        // Passwords
        $pass1          = SBCGetPassword::process($_POST['pass1']);
        $pass2          = SBCGetPassword::process($_POST['pass2']);
        if ($pass1 != $pass2)
        {
            SBC::userError('Passwords do not match');
        }

        // Create a hashed password instead
        $this->password = password_hash($pass1,PASSWORD_DEFAULT);

        // Terms of Service + Privacy Policy
        $this->termsofservice = isset($_POST['termsofservice']) ? (int) $_POST['termsofservice'] : 0;
        if ($this->termsofservice != 1)
        {
            SBC::userError('You must read and agree to the Terms of Service and Privacy Policy to register on this site.');
        }

        // Recaptcha
        $gRecaptchaResponse = $_POST['g-recaptcha-response'];
        $recaptcha          = new \ReCaptcha\ReCaptcha($recaptcha_settings['secret']);
        unset($recaptcha_settings);
        $resp = $recaptcha->verify($gRecaptchaResponse, $this->ip_address);
        if (!$resp->isSuccess()) {
            SBC::userError('Invalid Recaptcha. Please try again.');
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
        $method = 'UserRegistration->checkInfo()';

        // Double check
        if (empty($this->username) || empty($this->password))
        {
            SBC::devError('Odd... something is missing...',$method);
        }

        // Other vars
        if (empty($this->ip_address) || $this->rd < 1 || $this->time < 1)
        {
            SBC::devError('Something is missing...: ' . $this->ip_address . ' / ' . $this->rd . ' / ' . $this->time, $method);
        }
    }

    // Check if the user exists
    private function checkUsernameExists(&$db)
    {
        $method = 'UserRegistration->checkUsernameExists()';

        // Correct DB
        $db->sql_switch('sketchbookcafe');

        // Vars
        $username = $this->username;

        // Check if the username already exists
        $sql = 'SELECT id
            FROM users
            WHERE username=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('s',$username);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Check
        if ($row['id'] > 0)
        {
            SBC::userError('A user already exists with this username');
        }

        // Set as free
        $this->username_free = 1;
    }

    // Create a New User
    private function createNewUser(&$db)
    {
        $method = 'UserRegistration->createNewUser()';

        // Double check!
        if ($this->username_free != 1)
        {
            SBC::devError('Something went wrong! Username is not free...',$method);
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
        SBC::statementExecute($stmt,$db,$sql,$method);

        // Get User ID
        $sql = 'SELECT id
            FROM users
            WHERE username=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('s',$username);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // User ID?
        $this->user_id = $row['id'];
        if ($this->user_id < 1)
        {
            SBC::userError('Could not get new user_id from database. Please contact an administrator');
        }
    }
}