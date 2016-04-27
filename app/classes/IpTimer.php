<?php
// @author          Jonathan Maltezo (Kameloh)
// @lastUpdated     2016-04-27
// IP Timer Class for managing IP.. Timers
namespace SketchbookCafe\IpTimer;

use SketchbookCafe\SBC\SBC as SBC;

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
        $method = 'IpTimer->__construct()';

        // Get variables
        $this->ip_address = SBC::getIpAddress();
        $this->rd         = SBC::rd();

        // Make sure DB is set
        $db->sql_switch('sketchbookcafe');

        // Check IP Address
        $this->checkIpAddress($db);
    }

    // Check Timer
    public function checkTimer(&$db)
    {
        $method = 'IpTimer->checkTimer()';

        // Set vars
        $cooldown       = $this->cooldown;
        $column         = $this->column;
        $column_time    = 0;
        $time           = SBC::getTime();
        $time_left      = 0;
        $id             = $this->id;

        // Check
        if (empty($column) || $id < 1 || $time < 1 || $cooldown < 1)
        {
            SBC::devError('checkTimer error: $cooldown: '.$cooldown.', $column: '.$column.', $time: '.$time.', $id: '.$id,$method);
        }

        // Check table
        $db->sql_switch('sketchbookcafe');

        // Get the user's current timer
        $sql = 'SELECT '.$column.'
            FROM ip_timer
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Set vars
        $column_time    = $row[$column];

        // Time Left
        $time_left = $time - $column_time;
        if ($time_left < $cooldown)
        {
            SBC::userError('You must wait at least '.$cooldown.' second(s) before repeating this action');
        }
    }

    // Set Column
    public function setColumn($value)
    {
        $method = 'IpTimer->setColumn()';

        // Vars
        $cooldown = 0;
        $value = isset($value) ? $value : '';
        if (empty($value))
        {
            SBC::devError('$value is not set',$method);
        }

        // Switch
        switch ($value)
        {
            case 'action':          $column = 'action'; // general action - used for many things!
                                    $cooldown = 2; // 2 seconds
                                    break;

            case 'register':        $column = 'register';
                                    $cooldown = 300; // 5 minutes
                                    break;

            default:                $column = '';
                                    $cooldown = 0;
                                    break;
        }

        // Column
        $this->column = $column;
        if (empty($this->column))
        {
            SBC::devError('Could not set column',$method);
        }

        // Set Cooldown
        $this->cooldown = $cooldown;
    }

    // Update Timer
    public function update(&$db)
    {
        $method = 'IpTimer->update()';

        // Make sure database is correct
        $db->sql_switch('sketchbookcafe');

        // Set vars
        $column = $this->column;
        $time   = time();
        $id     = $this->id;

        // Check column
        if (empty($column))
        {
            SBC::devError('$column is empty for IpTimer->updateTimer(). Use setColumn() first',$method);
        }

        // Update IP Timer
        $sql = 'UPDATE ip_timer
            SET '.$column.'=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ii', $time,$id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }

    // Check IP Address if it exists
    private function checkIpAddress(&$db)
    {
        $method = 'IpTimer->checkIpAddress()';

        // Set vars
        $ip_address = $this->ip_address;

        // Check if IP address exists
        $sql = 'SELECT id 
            FROM ip_timer
            WHERE ip_address=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('s',$ip_address);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Do we have an IP?
        if ($row['id'] < 1)
        {
            // Insert new IP address
            $sql = 'INSERT INTO ip_timer
                SET ip_address=?';
            $stmt = $db->prepare($sql);
            $stmt->bind_param('s',$ip_address);
            SBC::statementExecute($stmt,$db,$sql,$method);

            // Get new ID
            $sql = 'SELECT id
                FROM ip_timer
                WHERE ip_address=?
                LIMIT 1';
            $stmt   = $db->prepare($sql);
            $stmt->bind_param('s',$ip_address);
            $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);
        }

        // Set vars
        $this->id = $row['id'];

        // Just in case
        if ($this->id < 1)
        {
            SBC::devError('Invalid ID for IpTimer()',$method);
        }
    }
}