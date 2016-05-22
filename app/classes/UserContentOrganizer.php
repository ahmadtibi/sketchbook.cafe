<?php
// @author          Kameloh
// @lastUpdated     2016-05-22
namespace SketchbookCafe\UserContentOrganizer;

use SketchbookCafe\SBC\SBC as SBC;

class UserContentOrganizer
{

    private $verified_entry = [];
    private $verified_user = [];

    private $db;

    // Construct
    public function __construct(&$db)
    {
        $this->db = &$db;
    }

    // Verify Entry
    final private function verifyEntry($entry_id)
    {
        $method = 'UserContentOrganizer->verifyEntry()';

        $db     = &$this->db;

        if ($entry_id < 1)
        {
            SBC::devError('Entry ID is not set',$method);
        }

        // Did we already verify the entry?
        if (isset($this->verified_entry[$entry_id]))
        {
            if ($this->verified_entry[$entry_id] == 1)
            {
                return null;
            }
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Entry Info
        $sql = 'SELECT id, isdeleted
            FROM challenge_entries
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$entry_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Verify
        $entry_id   = isset($row['id']) ? (int) $row['id'] : 0;
        if ($entry_id < 1)
        {
            SBC::devError('Cannot find entry in database',$method);
        }
        if ($row['isdeleted'] == 1)
        {
            SBC::devError('Entry no longer exists',$method);
        }

        // Mark user as verified
        $this->verified_entry[$entry_id] = 1;
    }

    // Verify User
    final private function verifyUser($user_id)
    {
        $method = 'UserContentOrganizer->verifyUser()';

        $db     = &$this->db;

        if ($user_id < 1)
        {
            SBC::devError('User ID is not set',$method);
        }

        // Did we already verify the user?
        if (isset($this->verified_user[$user_id]))
        {
            if ($this->verified_user[$user_id] == 1)
            {
                return null;
            }
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get user
        $sql = 'SELECT id
            FROM users
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$user_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Verify
        $user_id    = isset($row['id']) ? (int) $row['id'] : 0;
        if ($user_id < 1)
        {
            SBC::devError('Could not find user in database',$method);
        }

        // Mark user as verified
        $this->verified_user[$user_id] = 1;
    }

    // Note: Type 1-1 is blocked users OOPS
    // Add Entry as User Content
    // Type: 2-1
    final public function addContentEntry($user_id,$entry_id)
    {
        $method = 'UserContentOrganizer->addContentEntry()';

        // Verify Stuff
        $this->verifyUser($user_id);
        $this->verifyEntry($entry_id);

        // Initialize
        $db         = &$this->db;
        $table_name = 'u'.$user_id.'c';
        $type       = 2; // 2 entry
        $type2      = 1; // no other version
        $cid        = $entry_id;

        // Switch
        $db->sql_switch('sketchbookcafe_users');

        // Check if that content is already in their table
        $sql = 'SELECT id
            FROM '.$table_name.'
            WHERE type=?
            AND type2=?
            AND cid=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('iii',$type,$type2,$cid);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // ID?
        $id = isset($row['id']) ? (int) $row['id'] : 0;
        if ($id < 1)
        {
            // Add new entry
            $sql = 'INSERT INTO '.$table_name.'
                SET type=?,
                type2=?,
                cid=?';
            $stmt = $db->prepare($sql);
            $stmt->bind_param('iii',$type,$type2,$cid);
            SBC::statementExecute($stmt,$db,$sql,$method);
        }
    }

    // Count Content for Entries (type 2)
    final public function countContentEntry($user_id)
    {
        $method = 'UserContentOrganizer->countContentEntry()';

        // Verify Stuff
        $this->verifyUser($user_id);

        // Initialize
        $db         = &$this->db;
        $table_name = 'u'.$user_id.'c';

        // Switch
        $db->sql_switch('sketchbookcafe_users');

        // Count Content
        $sql = 'SELECT COUNT(*)
            FROM '.$table_name.'
            WHERE type=2';
        $result = $db->sql_query($sql);
        $row    = $db->sql_fetchrow($result);

        $total_entries  = isset($row[0]) ? (int) $row[0] : 0;

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Update totals
        $sql = 'UPDATE users
            SET total_entries=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ii',$total_entries,$user_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }
}