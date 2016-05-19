<?php
// @author          Kameloh
// @lastUpdated     2016-05-17
// Organizes user statistics
namespace SketchbookCafe\UserOrganizer;

use SketchbookCafe\SBC\SBC as SBC;

class UserOrganizer
{
    private $db;
    private $time = 0;
    private $user_id = 0;
    private $verified = [];

    // Construct
    public function __construct(&$db)
    {
        $this->db   = &$db;
        $this->time = SBC::getTime();
    }

    // Verify User
    final private function verifyUser($user_id)
    {
        $method = 'UserOrganizer->verifyUser()';

        $db     = &$this->db;

        // Already verified?
        if (isset($this->verified[$user_id]))
        {
            if ($this->verified[$user_id] == 1)
            {
                return null;
            }
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Verify if the user exists
        $sql = 'SELECT id
            FROM users
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$user_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Verify
        $temp_user_id    = isset($row['id']) ? (int) $row['id'] : 0;
        if ($temp_user_id < 1)
        {
            SBC::devError('Could not find user_id('.$user_id.') in database',$method);
        }

        // Set as verified
        $this->verified[$user_id] = 1;
    }

    // Total Forum Subscriptions
    final public function totalForumSubscriptions($user_id)
    {
        $method = 'UserOrganizer->totalForumSubscriptions()';

        $db     = &$this->db;

        // Verify User
        $this->verifyUser($user_id);

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Do they have a subscription table?
        $sql = 'SELECT table_forum_subscriptions
            FROM users
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$user_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Set
        $hastable   = $row['table_forum_subscriptions'];

        // If not, return null
        if ($hastable < 1)
        {
            return null;
        }

        // Switch
        $db->sql_switch('sketchbookcafe_users');

        // Table Name
        $table_name = 'u'.$user_id.'fs';

        // Count
        $sql = 'SELECT COUNT(*)
            FROM '.$table_name;
        $result = $db->sql_query($sql);
        $row    = $db->sql_fetchrow($result);

        // Total
        $total  = isset($row[0]) ? (int) $row[0] : 0;

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Update total subscribed threads
        $sql = 'UPDATE users
            SET total_thread_subscriptions=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ii',$total,$user_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }

    // Add Post Count for User
    final public function addPostCount($user_id)
    {
        $method = 'UserOrganizer->addPostCount()';

        // Verify User
        $this->verifyUser($user_id);

        // Initialize
        $db = &$this->db;

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Add a post
        $sql = 'UPDATE users
            SET total_posts=(total_posts + 1)
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$user_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }

    // Forum Thread Viewed
    final public function viewedThread($thread_id,$user_id)
    {
        $method = 'UserOrganizer->userViewedThread()';

        // Initialize
        $db         = &$this->db;
        $time       = $this->time;
        $table_name = 'u'.$user_id.'vt';

        // Verify User
        $this->verifyUser($user_id);

        // Thread ID set?
        if ($thread_id < 1)
        {
            SBC::devError('$thread_id is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Thread Info
        $sql = 'SELECT id, date_updated
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
            SBC::devError('Thread does not exist',$method);
        }

        // Set Date
        $date_updated = $row['date_updated'];

        // Switch
        $db->sql_switch('sketchbookcafe_users');

        // Check if the thread already exists in the user's table
        $sql = 'SELECT id
            FROM '.$table_name.'
            WHERE cid=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$thread_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Verify
        $id = isset($row['id']) ? (int) $row['id'] : 0;

        // Empty?
        if ($id < 1)
        {
            // Add
            $sql = 'INSERT INTO '.$table_name.'
                SET cid=?,
                lda=?,
                pda=?';
            $stmt = $db->prepare($sql);
            $stmt->bind_param('iii',$thread_id,$date_updated,$time);
            SBC::statementExecute($stmt,$db,$sql,$method);
        }
        else
        {
            // Update (use key instead of thread_id)
            $sql = 'UPDATE '.$table_name.'
                SET lda=?,
                pda=?
                WHERE id=?
                LIMIT 1';
            $stmt = $db->prepare($sql);
            $stmt->bind_param('iii',$date_updated,$time,$id);
            SBC::statementExecute($stmt,$db,$sql,$method);
        }
    }

    // Update Mail Timer
    final public function updateMailTimer($user_id)
    {
        $method = 'UserOrganizer->updateMailTimer()';

        // Initialize
        $db     = &$this->db;
        $time   = SBC::getTime();
        if ($user_id < 1)
        {
            return null;
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Update mailbox timer
        $sql = 'UPDATE users
            SET mailbox_update=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ii',$time,$user_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }
}