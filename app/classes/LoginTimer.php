<?php
// Login Timer : Allows a certain number of tries before a cooldown occurs

class LoginTimer
{
    private $ip_address = '';
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

    // Clean
    final public function clean(&$db)
    {
        // Initialize vars
        $time       = time();
        $cooldown   = 300; // 5 minutes
        $clean      = $time - $cooldown;

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Clean
        $sql = 'DELETE FROM login_timer
            WHERE date_created<'.$clean;
        $delete = $db->sql_query($sql);
    }

    // Check
    final public function check(&$db)
    {
        // Has Info?
        $this->hasinfo();

        // Clean
        $this->clean($db);

        // Initialize Vars
        $ip_address = $this->ip_address;

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Count up to 5
        $sql = 'SELECT id
            FROM login_timer
            WHERE ip_address=?
            LIMIT 5';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('s',$ip_address);
        if (!$stmt->execute())
        {
            error('Could not execute statement for LoginTimer->check()');
        }
        $result = $stmt->get_result();
        $rownum = $db->sql_numrows($result);
        $stmt->close();

        // Must not be greater or equal to 5
        if ($rownum >= 5)
        {
            error('Sorry, you must wait at least five minutes to relogin due to 5 or more failed login attempts.');
        }

    }

    // Insert
    final public function insert(&$db)
    {
        // Has info?
        $this->hasinfo();

        // Initialize Vars
        $ip_address = $this->ip_address;
        $time       = time();

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Insert
        $sql = 'INSERT INTO login_timer
            SET ip_address=?, 
            date_created=?';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('si',$ip_address,$time);
        if (!$stmt->execute())
        {
            error('Could not execute statement for LoginTimer->insert()');
        }
        $stmt->close();
    }
}