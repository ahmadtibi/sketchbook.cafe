<?php
// Main user class
class User
{
    public $id = 0;
    private $session_id = 0;
    private $session_code = '';
    private $ip_address = '';

    // Settings
    public $username = 'Guest';
    private $auth_type = 0; // 1 optional, 2 required
    private $timezone_my = 'America/Los_Angeles';
    private $timezone_id = 6;

    // Generated Variables
    private $dtzone = '';
    private $loggedin = 0;

    // Db
    private $data;

    // Construct
    public function __construct(&$user_settings)
    {
        $this->id = (int) $user_settings['id'];
        if ($this->id < 1)
        {
            $this->id = 0;
        }
    }

    // Logged In
    public function loggedIn()
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
}