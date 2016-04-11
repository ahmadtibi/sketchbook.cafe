<?php
// Login Timer : Allows a certain number of tries before a cooldown occurs

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
        $this->ip_address   = $_SERVER['REMOTE_ADDR'];

        // Just in case something goes wrong
        if (empty($this->ip_address))
        {
            error('Dev error: $ip_address is not set for LoginTimer->construct()');
        }

        // Has Info
        $this->hasinfo = 1;
    }

    // Has Info
    final public function hasinfo()
    {
        if ($this->hasinfo != 1)
        {
            error('Dev error: $hasinfo is not set for LoginTimer->hasinfo()');
        }
    }

    // Failed Login Attempt
    final public function failedLogin(&$db)
    {
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
            error('Dev error: $id is not set for LoginTimer->failedLogin()');
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
        if (!$stmt->execute())
        {
            error('Could not execute statement (update login tries) or LoginTimer->failedLogin()');
        }
        $stmt->close();

        // Calculate number of tries
        $tries_left = $max_tries - ($login_tries + 1); // add since it's an attempt

        // Give an error if the user has 0 tries left
        if ($tries_left < 1)
        {
            error('Sorry, you have to wait at least give minutes to relogin due to 5 or more failed login attempts.');
        }

        // Dev note: end with an error
    }

    // Check
    final public function check(&$db)
    {
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
        $stmt = $db->prepare($sql);
        $stmt->bind_param('s',$ip_address);
        if (!$stmt->execute())
        {
            error('Could not execute statement for LoginTimer->check()');
        }
        $result = $stmt->get_result();
        $row    = $db->sql_fetchrow($result);
        $stmt->close();
        $db->sql_freeresult($result);

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
            if (!$stmt->execute())
            {
                error('Could not execute statement (insert new IP) in LoginTimer->check()');
            }
            $stmt->close();

            // Get id of IP
            $sql = 'SELECT id 
                FROM login_timer
                WHERE ip_address=?
                LIMIT 1';
            $stmt = $db->prepare($sql);
            $stmt->bind_param('s',$ip_address);
            if (!$stmt->execute())
            {
                error('Could not execute statement (get new IP) in LoginTimer->check()');
            }
            $result = $stmt->get_result();
            $row    = $db->sql_fetchrow($result);
            $stmt->close();

            // Set ID again
            $id = isset($row['id']) ? (int) $row['id'] : 0;
            if ($id < 1)
            {
                error('Dev error: could not get new IP address in LoginTimer->check()');
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
            if (!$stmt->execute())
            {
                error('Could not execute statement (reset login timer) for LoginTimer->check()');
            }
            $stmt->close();

            // Reset Tries
            $login_tries = 0;
        }

        // Check number of tries
        if ($login_tries >= $max_tries)
        {
            error('Sorry, you must wait at least five minutes to relogin due to 5 or more failed login attemps');
        }

        // Set Vars
        $this->id           = $id;
        $this->login_last   = $login_last;
        $this->login_tries  = $login_tries;
    }
}