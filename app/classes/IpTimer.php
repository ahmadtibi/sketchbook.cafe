<?php
// IP Timer Class for managing IP.. Timers
class IpTimer
{
    private $ip_address = '';
    private $id = 0;
    private $rd = 0;
    private $column = '';
    private $cooldown = 0;

    // Construct
    public function __construct(&$db)
    {
        // Get variables
        $this->ip_address = $_SERVER['REMOTE_ADDR'];
        $this->rd         = rand(100000,9999999);

        // Make sure DB is set
        $db->sql_switch('sketchbookcafe');

        // Check IP Address
        $this->checkIpAddress($db);
    }

    // Check Timer
    public function checkTimer(&$db)
    {
        // Set vars
        $cooldown       = $this->cooldown;
        $column         = $this->column;
        $column_time    = 0;
        $time           = time();
        $time_left      = 0;
        $id             = $this->id;

        // Check
        if (empty($column) || $id < 1 || $time < 1 || $cooldown < 1)
        {
            error('Dev error: checkTimer error: $cooldown: '.$cooldown.', $column: '.$column.', $time: '.$time.', $id: '.$id);
        }

        // Check table
        $db->sql_switch('sketchbookcafe');

        // Get the user's current timer
        $sql = 'SELECT '.$column.'
            FROM ip_timer
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$id);
        if (!$stmt->execute())
        {
            error('Could not execute statement for IpTimer->checkTimer()');
        }
        $result = $stmt->get_result();
        $row    = $db->sql_fetchrow($result);
        $stmt->close();

        // Set vars
        $column_time    = $row[$column];

        // Time Left
        $time_left = $time - $column_time;
        if ($time_left < $cooldown)
        {
            error('You must wait at least '.$cooldown.' second(s) before repeating this action');
        }
    }

    // Set Column
    public function setColumn($value)
    {
        // Vars
        $cooldown = 0;
        $value = isset($value) ? $value : '';
        if (empty($value))
        {
            error('Dev error: $value is not set for IpTimer->setColumn()');
        }

        // Switch
        switch ($value)
        {
            case 'register':    $column = 'register';
                                $cooldown = 300; // 5 minutes
                                break;

            default:            $column = '';
                                $cooldown = 0;
                                break;
        }

        // Column
        $this->column = $column;
        if (empty($this->column))
        {
            error('Could not set column for IpTimer->setColumn()');
        }

        // Set Cooldown
        $this->cooldown = $cooldown;
    }

    // Update Timer
    public function update(&$db)
    {
        // Set vars
        $column = $this->column;
        $time   = time();
        $id     = $this->id;

        // Check column
        $column = $this->column;
        if (empty($column))
        {
            error('$column is empty for IpTimer->updateTimer(). Use setColumn() first!');
        }

        // Update IP Timer
        $sql = 'UPDATE ip_timer
            SET '.$column.'=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ii', $time,$id);
        if (!$stmt->execute())
        {
            error('Could not execute statement for IpTimer->update()');
        }
        $stmt->close();
    }

    // Check IP Address if it exists
    private function checkIpAddress(&$db)
    {
        // Set vars
        $ip_address = $this->ip_address;

        // Check if IP address exists
        $sql = 'SELECT id 
            FROM ip_timer
            WHERE ip_address=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('s',$ip_address);
        if (!$stmt->execute())
        {
            error('Could not execute statement for IpTimer->checkIpAddress()');
        }
        $result = $stmt->get_result();
        $row    = $db->sql_fetchrow($result);
        $stmt->close();

        // Do we have an IP?
        if ($row['id'] < 1)
        {
            // Insert new IP address
            $sql = 'INSERT INTO ip_timer
                SET ip_address=?';
            $stmt = $db->prepare($sql);
            $stmt->bind_param('s',$ip_address);
            if (!$stmt->execute())
            {
                error('Could not insert new IP address into database for IpTimer->checkIpAddress()');
            }
            $stmt->close();

            // Get new ID
            $sql = 'SELECT id
                FROM ip_timer
                WHERE ip_address=?
                LIMIT 1';
            $stmt = $db->prepare($sql);
            $stmt->bind_param('s',$ip_address);
            if (!$stmt->execute())
            {
                error('Could not get newly inserted IP address from database for IpTimer->checkIpAddress()');
            }
            $result = $stmt->get_result();
            $row    = $db->sql_fetchrow($result);
            $stmt->close();
        }

        // Set vars
        $this->id = $row['id'];
        if ($this->id < 1)
        {
            error('Invalid ID for IpTimer()');
        }
    }

}