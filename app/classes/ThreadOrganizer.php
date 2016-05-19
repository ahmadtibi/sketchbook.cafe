<?php
// @author          Kameloh
// @lastUpdated     2016-05-08
namespace SketchbookCafe\ThreadOrganizer;

use SketchbookCafe\SBC\SBC as SBC;

class ThreadOrganizer
{
    private $db;
    private $verified = [];
    private $thread_forum_id = [];
    private $thread_user_id = [];
    private $thread_date_bumped = [];
    private $thread_total_comments = [];

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
        $sql = 'SELECT id, forum_id, user_id, date_bumped, total_comments
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

        // Set Vars
        $this->verified[$thread_id]                 = 1;
        $this->thread_forum_id[$thread_id]          = $row['forum_id'];
        $this->thread_user_id[$thread_id]           = $row['user_id'];
        $this->thread_date_bumped[$thread_id]       = $row['date_bumped'];
        $this->thread_total_comments[$thread_id]    = $row['total_comments'];
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

    // Count Unique Users
    final public function countUniqueUsers($thread_id)
    {
        $method = 'ThreadOrganizer->countUniqueUsers()';

        // Verify Thread
        $this->verifyThread($thread_id);

        // Initialize
        $db             = &$this->db;
        $thread_user_id = $this->thread_user_id[$thread_id];

        // Check
        if ($thread_user_id < 1)
        {
            SBC::devError('$thread_user_id is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe_forums');

        // Count Unique Users
        $table_name = 't'.$thread_id.'d';
        $sql = 'SELECT DISTINCT(uid)
            FROM '.$table_name.'
            WHERE uid!='.$thread_user_id;
        $result = $db->sql_query($sql);
        $rownum = $db->sql_numrows($result);
        $db->sql_freeresult($result);

        // Total
        $total = isset($rownum) ? (int) $rownum : 0;
        $total += 1; // add thread owner

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Update Thread
        $sql = 'UPDATE forum_threads
            SET total_users=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ii',$total,$thread_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }

    // Count Total Replies
    final public function countTotalReplies($thread_id)
    {
        $method = 'ThreadOrganizer->countTotalReplies()';

        // Verify Thread
        $this->verifyThread($thread_id);

        // Initialize
        $db = &$this->db;

        // Switch
        $db->sql_switch('sketchbookcafe_forums');

        // Count
        $table_name = 't'.$thread_id.'d';
        $sql = 'SELECT COUNT(*)
            FROM '.$table_name;
        $result = $db->sql_query($sql);
        $row    = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);

        // Total
        $total = isset($row[0]) ? (int) $row[0] : 0;

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Update Total Replies
        $sql = 'UPDATE forum_threads
            SET total_comments=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ii',$total,$thread_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }

    // Update Last Post Info
    final public function updateLastPostInfo($thread_id)
    {
        $method = 'ThreadOrganizer->updateLastPostInfo()';

        // Verify Thread
        $this->verifyThread($thread_id);

        // Initialize
        $db                 = &$this->db;
        $table_name         = 't'.$thread_id.'d';
        $comment_user_id    = 0;

        // Switch
        $db->sql_switch('sketchbookcafe_forums');

        // Get last comment ID
        $sql = 'SELECT cid
            FROM '.$table_name.'
            ORDER BY id
            DESC
            LIMIT 1';
        $result = $db->sql_query($sql);
        $row    = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);

        // Check comment
        $comment_id = isset($row['cid']) ? (int) $row['cid'] : 0;

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Update comment
        if ($comment_id > 0)
        {
            // Get comment info
            $sql = 'SELECT id, user_id
                FROM sbc_comments
                WHERE id=?
                LIMIT 1';
            $stmt   = $db->prepare($sql);
            $stmt->bind_param('i',$comment_id);
            $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

            // Comment Info
            $comment_id = isset($row['id']) ? (int) $row['id'] : 0;
            if ($comment_id > 0)
            {
                $comment_user_id = $row['user_id'];
            }
        }

        // Update Forum Thread
        $sql = 'UPDATE forum_threads
            SET last_user_id=?,
            last_comment_id=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('iii',$comment_user_id,$comment_id,$thread_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }

    // Update bump date
    final public function updateBumpDate($thread_id)
    {
        $method = 'ThreadOrganizer->updateBumpDate()';

        // Verify Thread
        $this->verifyThread($thread_id);

        // Initialize
        $db             = &$this->db;
        $date_bumped    = (int) $this->thread_date_bumped[$thread_id];
        $forum_id       = (int) $this->thread_forum_id[$thread_id];
        $table_name     = 'forum'.$forum_id.'x';

        // Check
        if ($forum_id < 1)
        {
            SBC::devError('$forum_id is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe_forums');

        // Update
        $sql = 'UPDATE '.$table_name.'
            SET date_bumped=?
            WHERE thread_id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ii',$date_bumped,$thread_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }

    // Get Total Comments
    final public function getTotalComments($thread_id)
    {
        $method = 'ThreadOrganizer->getTotalComments()';

        // Verify Thread
        $this->verifyThread($thread_id);

        return (int) $this->thread_total_comments[$thread_id];
    }

    // Get Forum ID
    final public function getForumId($thread_id)
    {
        $method = 'ThreadOrganizer->getForumId()';

        // Verify Thread
        $this->verifyThread($thread_id);

        // Set Forum ID
        $forum_id = $this->thread_forum_id[$thread_id];
        if ($forum_id < 1)
        {
            SBC::devError('Forum ID is not set',$method);
        }

        return $forum_id;
    }
}