<?php
// Login Timer : Allows a certain number of tries before a cooldown occurs
namespace SketchbookCafe\LoginTimer;

use SketchbookCafe\SBC\SBC as SBC;

class LoginTimer
{
    private $id = 0;
    private $ip_address = '';
    private $date_updated = 0;
    private $login_tries = 0;
    private $login_last = 0;
    private $hasinfo = 0;

    // Construct
    public function __construct()
    {
        $method = 'LoginTimer->__construct()';

        // Set Vars
        $this->ip_address   = SBC::getIpAddress();

        // Just in case something goes wrong
        if (empty($this->ip_address))
        {
            SBC::devError('$ip_address is not set',$method);
        }

        // Has Info
        $this->hasinfo = 1;
    }

    // Has Info
    final public function hasinfo()
    {
        $method = 'LoginTimer->hasinfo()';

        if ($this->hasinfo != 1)
        {
            SBC::devError('$hasinfo is not set',$method);
        }
    }

    // Failed Login Attempt
    final public function failedLogin(&$db)
    {
        $method = 'LoginTimer->failedLogin()';

        // Has info?
        $this->hasinfo();

        // Initialize Vars
        $time           = time();
        $tries_left     = 0;
        $max_tries      = 5;
        $login_last     = $this->login_last;
        $login_tries    = $this->login_tries;
        $id             = $this->id;
        if ($id < 1)
        {
            SBC::devError('$id is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Update login tries
        $sql = 'UPDATE login_timer
            SET login_last=?,
            login_tries=(login_tries + 1)
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ii',$time,$id);
        SBC::statementExecute($stmt,$db,$sql,$method);

        // Calculate number of tries
        $tries_left = $max_tries - ($login_tries + 1); // add since it's an attempt

        // Give an error if the user has 0 tries left
        if ($tries_left < 1)
        {
            SBC::userError('Sorry, you have to wait at least give minutes to relogin due to 5 or more failed login attempts.');
        }
    }

    // Check
    final public function check(&$db)
    {
        $method = 'LoginTimer->check()';

        // Has Info?
        $this->hasinfo();

        // Initialize Vars
        $ip_address     = $this->ip_address;
        $id             = 0;
        $login_last     = 0;
        $login_tries    = 0;
        $max_tries      = 5; // default
        $cooldown       = 300; // 300 seconds : 5 minutes
        $time           = time();
        $current_time   = 0;

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Check if an IP already exists
        $sql = 'SELECT id, login_last, login_tries
            FROM login_timer
            WHERE ip_address=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('s',$ip_address);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // ID?
        $id             = $row['id'];
        $login_last     = $row['login_last'];
        $login_tries    = $row['login_tries'];

        // Do we have an IP?
        if ($id < 1)
        {
            // Add IP
            $sql = 'INSERT INTO login_timer
                SET ip_address=?, 
                date_created=?,
                date_updated=?';
            $stmt = $db->prepare($sql);
            $stmt->bind_param('sii',$ip_address,$time,$time);
            SBC::statementExecute($stmt,$db,$sql,$method);

            // Get id of IP
            $sql = 'SELECT id 
                FROM login_timer
                WHERE ip_address=?
                LIMIT 1';
            $stmt   = $db->prepare($sql);
            $stmt->bind_param('s',$ip_address);
            $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

            // Set ID again
            $id = isset($row['id']) ? (int) $row['id'] : 0;
            if ($id < 1)
            {
                SBC::devError('could not get new IP address',$method);
            }
        }

        // Calculate Time
        $current_time   = $time - $login_last;
        if ($current_time >= $cooldown)
        {
            // Reset the timer if it's over cooldown
            $sql = 'UPDATE login_timer
                SET login_last=?,
                login_tries=0
                WHERE id=?
                LIMIT 1';
            $stmt = $db->prepare($sql);
            $stmt->bind_param('ii',$time,$id);
            SBC::statementExecute($stmt,$db,$sql,$method);

            // Reset Tries
            $login_tries = 0;
        }

        // Check number of tries
        if ($login_tries >= $max_tries)
        {
            SBC::userError('Sorry, you must wait at least five minutes to relogin due to 5 or more failed login attempts');
        }

        // Set Vars
        $this->id           = $id;
        $this->login_last   = $login_last;
        $this->login_tries  = $login_tries;
    }
}