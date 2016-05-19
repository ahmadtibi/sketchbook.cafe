<?php
// @author          Kameloh
// @lastUpdated     2016-05-08
namespace SketchbookCafe\ForumOrganizer;

use SketchbookCafe\SBC\SBC as SBC;

class ForumOrganizer
{
    private $db;
    private $verified = [];

    // Construct
    public function __construct(&$db)
    {
        $this->db = &$db;
    }

    // Verify Forum
    final private function verifyForum($forum_id)
    {
        $method = 'ForumOrganizer->verifyForum()';

        // Initialize
        $db     = &$this->db;
        if ($forum_id < 1)
        {
            SBC::devError('$forum_id is not set',$method);
        }

        // Verified?
        if (isset($this->verified[$forum_id]))
        {
            if ($this->verified[$forum_id] == 1)
            {
                return null;
            }
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Forum Info
        $sql = 'SELECT id, isforum
            FROM forums
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$forum_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Verify
        $forum_id   = isset($row['id']) ? (int) $row['id'] : 0;
        if ($forum_id < 1)
        {
            SBC::devError('Could not find forum in database',$method);
        }

        // Make sure it's a forum
        if ($row['isforum'] != 1)
        {
            SBC::devError('Odd, this is not a forum ('.$forum_id.')',$method);
        }

        // Mark as Verified
        $this->verified[$forum_id] = 1;
    }

    // Add One Post
    final public function addOnePostCount($forum_id)
    {
        $method = 'ForumOrganizer->addOnePostCount()';

        // Verify Forum
        $this->verifyForum($forum_id);

        // Initialize
        $db = &$this->db;

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Update Forum
        $sql = 'UPDATE forums
            SET total_posts=(total_posts + 1)
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$forum_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }

    // Update Last Post Info
    final public function updateLastPostInfo($forum_id)
    {
        $method = 'ForumOrganizer->updateLastPostInfo()';

        // Verify Forum
        $this->verifyForum($forum_id);

        // Initialize
        $db             = &$this->db;
        $table_name     = 'forum'.$forum_id.'x';
        $last_user_id   = 0;
        $last_thread_id = 0;

        // Switch
        $db->sql_switch('sketchbookcafe_forums');

        // Get Last Thread
        $sql = 'SELECT thread_id
            FROM '.$table_name.'
            WHERE is_sticky=0
            ORDER BY date_updated
            DESC
            LIMIT 1';
        $result = $db->sql_query($sql);
        $row    = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);

        // Check
        $thread_id  = isset($row['thread_id']) ? (int) $row['thread_id'] : 0;

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Thread Info
        if ($thread_id > 0)
        {
            // Get info
            $sql = 'SELECT id, last_user_id
                FROM forum_threads
                WHERE id=?
                LIMIT 1';
            $stmt   = $db->prepare($sql);
            $stmt->bind_param('i',$thread_id);
            $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

            // Verify
            $thread_id  = isset($row['id']) ? (int) $row['id'] : 0;
            if ($thread_id > 0)
            {
                $last_user_id   = $row['last_user_id'];
                $last_thread_id = $row['id'];
            }
        }

        // Update Forum Info
        $sql = 'UPDATE forums
            SET last_user_id=?,
            last_thread_id=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('iii',$last_user_id,$last_thread_id,$forum_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }

    // Count Total Threads for Forum
    final public function countTotalThreads($forum_id)
    {
        $method = 'ForumOrganizer->countTotalThreads()';

        // Verify Forum
        $this->verifyForum($forum_id);

        // Initialize
        $db         = &$this->db;
        $table_name = 'forum'.$forum_id.'x';

        // Switch
        $db->sql_switch('sketchbookcafe_forums');

        // Count Threads
        $sql = 'SELECT COUNT(*)
            FROM '.$table_name;
        $result = $db->sql_query($sql);
        $row    = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);

        // Total
        $total = isset($row[0]) ? (int) $row[0] : 0;

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Update Forum Stats
        $sql = 'UPDATE forums
            SET total_threads=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ii',$total,$forum_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }

}