<?php
// @author          Kameloh
// @lastUpdated     2016-05-17
namespace SketchbookCafe\PointsOrganizer;

use SketchbookCafe\SBC\SBC as SBC;

class PointsOrganizer
{
    private $entry_row = [];
    private $verified_entry = [];

    private $db;

    public function __construct(&$db)
    {
        $this->db = &$db;
    }

    // Verify Entry
    final private function verifyEntry($entry_id)
    {
        $method = 'PointsOrganizer->verifyEntry()';

        if ($entry_id < 1)
        {
            SBC::devError('Entry ID is not set',$method);
        }

        $db     = &$this->db;

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
        $sql = 'SELECT id, challenge_id, user_id
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
            SBC::devError('Could not find entry in database',$method);
        }

        // Set Vars
        $this->entry_row[$entry_id] = $row;
        $this->verified_entry[$entry_id] = 1;
    }

    // Add Points by Entry
    final public function addPointsByEntry($entry_id)
    {
        $method = 'PointsOrganizer->addPointsByEntry()';

        // Verify Entry
        $this->verifyEntry($entry_id);

        // Initialize
        $db             = &$this->db;
        $points         = 0;
        $time           = SBC::getTime();
        $user_id        = isset($this->entry_row[$entry_id]) ? (int) $this->entry_row[$entry_id]['user_id'] : 0;
        $challenge_id   = isset($this->entry_row[$entry_id]) ? (int) $this->entry_row[$entry_id]['challenge_id'] : 0;
        if ($user_id < 1)
        {
            SBC::devError('User ID is not set',$method);
        }
        if ($challenge_id < 1)
        {
            SBC::devError('Challenge ID is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Verify Challenge + Get Points Info
        $sql = 'SELECT id, points
            FROM challenges
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$challenge_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Verify
        $challenge_id   = isset($row['id']) ? (int) $row['id'] : 0;
        if ($challenge_id < 1)
        {
            SBC::devError('Could not find challenge ID in database',$method);
        }
        $points = (int) $row['points'];
        if ($points < 1)
        {
            SBC::devError('Invalid points for challenge',$method);
        }

        // Check Points Log to see if we've already added points
        $sql = 'SELECT id
            FROM points_log
            WHERE user_id=?
            AND entry_id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('ii',$user_id,$entry_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // ID
        $log_id = isset($row['id']) ? (int) $row['id'] : 0;
        if ($log_id > 1)
        {
            return null;
        }

        // Insert into logs
        $sql = 'INSERT INTO points_log
            SET user_id=?,
            entry_id=?,
            challenge_id=?,
            date_created=?,
            points=?,
            isdeleted=1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('iiiii',$user_id,$entry_id,$challenge_id,$time,$points);
        SBC::statementExecute($stmt,$db,$sql,$method);

        // Get new ID
        $sql = 'SELECT id
            FROM points_log
            WHERE user_id=?
            AND entry_id=?
            AND isdeleted=1
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('ii',$user_id,$entry_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        $log_id = isset($row['id']) ? (int) $row['id'] : 0;
        if ($log_id < 1)
        {
            SBC::devError('Could not insert new points log into database',$method);
        }

        // Add Points for User
        $sql = 'UPDATE users
            SET sketch_points=(sketch_points + '.$points.')
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$user_id);
        SBC::statementExecute($stmt,$db,$sql,$method);

        // Update Points ID
        $sql = 'UPDATE points_log
            SET isdeleted=0
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$log_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }
}