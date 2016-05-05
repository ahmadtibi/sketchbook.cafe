<?php
// @author          Kameloh
// @lastUpdated     2016-05-03
namespace SketchbookCafe\ThreadOrganizer;

use SketchbookCafe\SBC\SBC as SBC;

class ThreadOrganizer
{
    private $db;
    private $verified = [];

    // Construct
    public function __construct(&$db)
    {
        $this->db = $db;
    }

    // Verify Thread
    final private function verifyThread($thread_id)
    {
        $method = 'ThreadOrganizer->verifyThread()';

        // Initialize
        $db     = &$this->db;
        $thread_id = isset($thread_id) ? (int) $thread_id : 0;
        if ($thread_id < 1)
        {
            SBC::devError('$thread_id is not set',$method);
        }

        // Verified?
        if (isset($this->verified[$thread_id]))
        {
            if ($this->verified[$thread_id] == 1)
            {
                return null;
            }
        }

        $db->sql_switch('sketchbookcafe');

        // Get thread info
        $sql = 'SELECT id
            FROM forum_threads
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$thread_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Verify
        $thread_id  = isset($row['id']) ? (int) $row['id'] : 0;
        if ($thread_id < 1)
        {
            SBC::devError('Could not find thread in database',$method);
        }

        // Set as verified
        $this->verified[$thread_id] = 1;
    }

    // View Count Update
    final public function viewCountUpdate($thread_id,$user_id)
    {
        $method = 'ThreadOrganizer->viewCountUpdate()';

        // User ID not required...
        $user_id    = isset($user_id) ? (int) $user_id : 0;
        $thread_id  = isset($thread_id) ? (int) $thread_id : 0;
        if ($thread_id < 1)
        {
            SBC::devError('$thread_id is not set',$method);
        }

        // Verify Thread
        $this->verifyThread($thread_id);

        // User or IP Address?
        if ($user_id < 1)
        {
            $this->viewCountUpdateByIP($thread_id);
        }
        else
        {
            $this->viewCountUpdateByUserID($thread_id,$user_id);
        }
    }

    // View Count by User ID
    final private function viewCountUpdateByUserID($thread_id,$user_id)
    {
        $method = 'ThreadOrganizer->viewCountUpdateByUserID()';

        // Initialize
        $db         = &$this->db;
        $time       = SBC::getTime();
        $thread_id  = SBC::checkNumber($thread_id,'$thread_id');
        $user_id    = isset($user_id) ? (int) $user_id : 0;
        if ($user_id < 1)
        {
            SBC::devError('$user_id is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Check if user exists
        $sql = 'SELECT id, date_updated
            FROM views_users
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$user_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Set?
        $id = isset($row['id']) ? (int) $row['id'] : 0;
        if ($id < 1)
        {
            // Add User
            $sql = 'INSERT INTO views_users
                SET id=?';
            $stmt = $db->prepare($sql);
            $stmt->bind_param('i',$user_id);
            SBC::statementExecute($stmt,$db,$sql,$method);

            // Get ID
            $sql = 'SELECT id, date_updated
                FROM views_users
                WHERE id=?
                LIMIT 1';
            $stmt   = $db->prepare($sql);
            $stmt->bind_param('i',$user_id);
            $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

            // Check
            $id = isset($row['id']) ? (int) $row['id'] : 0;
            if ($id < 1)
            {
                SBC::devError('Could not insert user_id('.$user_id.') in views_users',$method);
            }
        }

        // Set Date
        $cooldown       = 5;
        $date_updated   = $row['date_updated'];

        $time_left  = $time - $date_updated;
        if ($time_left > $cooldown)
        {
            // Update Thread View Count
            $this->updateViewCount($thread_id);

            // Switch
            $db->sql_switch('sketchbookcafe');

            // Update Timer
            $sql = 'UPDATE views_users
                SET date_updated=?
                WHERE id=?
                LIMIT 1';
            $stmt = $db->prepare($sql);
            $stmt->bind_param('ii',$time,$id);
            SBC::statementExecute($stmt,$db,$sql,$method);
        }
    }

    // View Count by IP Address
    final private function viewCountUpdateByIP($thread_id)
    {
        $method = 'ThreadOrganizer->viewCountUpdateByIP()';

        // Initialize
        $db         = &$this->db;
        $ip_address = SBC::getIpAddress();
        $time       = SBC::getTime();
        $thread_id  = isset($thread_id) ? (int) $thread_id : 0;
        if ($thread_id < 1)
        {
            SBC::devError('$thread_id is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Check IP
        $sql = 'SELECT id, date_updated
            FROM views_ip
            WHERE ip_address=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('s',$ip_address);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Verify
        $id = isset($row['id']) ? (int) $row['id'] : 0;
        if ($id < 1)
        {
            // Create IP
            $sql = 'INSERT INTO views_ip
                SET ip_address=?';
            $stmt = $db->prepare($sql);
            $stmt->bind_param('s',$ip_address);
            SBC::statementExecute($stmt,$db,$sql,$method);

            // Get New ID
            $sql = 'SELECT id, date_updated
                FROM views_ip
                WHERE ip_address=?
                LIMIT 1';
            $stmt   = $db->prepare($sql);
            $stmt->bind_param('s',$ip_address);
            $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

            // Set ID
            $id = isset($row['id']) ? (int) $row['id'] : 0;
            if ($id < 1)
            {
                SBC::devError('Could not insert new IP into views_ip',$method);
            }
        }

        // Set Date
        $cooldown       = 15;
        $date_updated   = $row['date_updated'];

        $time_left  = $time - $date_updated;
        if ($time_left > $cooldown)
        {
            // Update Thread View Count
            $this->updateViewCount($thread_id);

            // Switch
            $db->sql_switch('sketchbookcafe');

            // Update Timer
            $sql = 'UPDATE views_ip
                SET date_updated=?
                WHERE id=?
                LIMIT 1';
            $stmt = $db->prepare($sql);
            $stmt->bind_param('ii',$time,$id);
            SBC::statementExecute($stmt,$db,$sql,$method);
        }
    }

    // Update View Count for Thread
    final private function updateViewCount($thread_id)
    {
        $method = 'ThreadOrganizer->updateViewCount()';

        // Initialize
        $db         = &$this->db;
        $thread_id  = SBC::checkNumber($thread_id,'$thread_id');

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Update View Count
        $sql = 'UPDATE forum_threads
            SET total_views=(total_views + 1)
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$thread_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }
}