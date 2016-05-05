<?php
// @author          Kameloh
// @lastUpdated     2016-05-03
namespace SketchbookCafe\OnlineOrganizer;

use SketchbookCafe\SBC\SBC as SBC;

class OnlineOrganizer
{
    private $user_id = 0;
    private $ip_address = '';
    private $time = 0;

    private $db;

    // Construct
    public function __construct(&$db)
    {
        $this->db = &$db;
    }

    // Clean Online List
    final public function clean()
    {
        $method = 'OnlineOrganizer';

        // Initialize
        $db     = &$this->db;

        // Clean Timer
        $time           = SBC::getTime();
        $clean_seconds  = 86400; // 24 hours
        $clean_timer    = $time - $clean_seconds;

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Clear Users
        $sql = 'DELETE FROM online_users
            WHERE date_updated<'.$clean_timer;
        $db->sql_query($sql);

        // Clear IPs
        $sql = 'DELETE FROM online_ip
            WHERE date_updated<'.$clean_timer;
        $db->sql_query($sql);
    }

    // Update User
    final public function updateUser($user_id)
    {
        $method = 'OnlineOrganizer->process()';

        // Initialize
        $db                 = &$this->db;

        // Check User ID
        $this->user_id  = isset($user_id) ? (int) $user_id : 0;
        if ($this->user_id < 1)
        {
            $this->user_id = 0;
        }

        // User or IP?
        if ($this->user_id > 0)
        {
            $this->processUser();
        }
        else
        {
            $this->processIP();
        }
    }

    // Process IP
    final private function processIP()
    {
        $method = 'OnlineOrganizer->processIP()';

        // Initialize
        $db         = &$this->db;
        $ip_address = SBC::getIpAddress();
        $time       = SBC::getTime();

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Check IP
        $sql = 'SELECT id
            FROM online_ip
            WHERE ip_address=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('s',$ip_address);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // ID?
        $id = isset($row['id']) ? (int) $row['id'] : 0;
        if ($id < 1)
        {
            // Add IP
            $sql = 'INSERT INTO online_ip
                SET ip_address=?,
                date_updated=?';
            $stmt = $db->prepare($sql);
            $stmt->bind_param('si',$ip_address,$time);
            SBC::statementExecute($stmt,$db,$sql,$method);

            // Find ID
            $sql = 'SELECT id
                FROM online_ip
                WHERE ip_address=?
                LIMIT 1';
            $stmt   = $db->prepare($sql);
            $stmt->bind_param('s',$ip_address);
            $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

            // Check
            $id = isset($row['id']) ? (int) $row['id'] : 0;
            if ($id < 1)
            {
                SBC::devError('Could not insert IP address into online_ip',$method);
            }
        }

        // Update Timer
        $sql = 'UPDATE online_ip
            SET date_updated=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ii',$time,$id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }

    // Process User
    final private function processUser()
    {
        $method = 'OnlineOrganizer->processUser()';

        // Initialize
        $db         = &$this->db;
        $user_id    = SBC::checkNumber($this->user_id,'$this->user_id');
        $time       = SBC::getTime();

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Check
        $sql = 'SELECT id
            FROM online_users
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$user_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // ID exist?
        $id = isset($row['id']) ? (int) $row['id'] : 0;
        if ($id < 1)
        {
            // Insert
            $sql = 'INSERT INTO online_users
                SET id=?,
                date_updated=?';
            $stmt = $db->prepare($sql);
            $stmt->bind_param('ii',$user_id,$time);
            SBC::statementExecute($stmt,$db,$sql,$method);

            // Double check?
            $sql = 'SELECT id
                FROM online_users
                WHERE id=?
                LIMIT 1';
            $stmt   = $db->prepare($sql);
            $stmt->bind_param('i',$user_id);
            $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

            $id = isset($row['id']) ? (int) $row['id'] : 0;
            if ($id < 1)
            {
                SBC::devError('Could not insert user_id into online_users',$method);
            }
        }

        // Update Timer
        $sql = 'UPDATE online_users
            SET date_updated=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ii',$time,$user_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }
}