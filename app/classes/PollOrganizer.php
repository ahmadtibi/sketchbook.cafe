<?php
// @author          Kameloh
// @lastUpdated     2016-05-01
namespace SketchbookCafe\PollOrganizer;

use SketchbookCafe\SBC\SBC as SBC;

class PollOrganizer
{
    private $poll_id = 0;
    private $is_verified = 0;
    private $db;

    // Construct
    public function __construct(&$db)
    {
        $this->db = &$db;
    }

    // Verify Poll
    final private function verifyPoll($poll_id)
    {
        $method = 'PollOrganizer->verifyPoll()';
        $db     = &$this->db;
        if ($poll_id < 1)
        {
            SBC::devError('$poll_id is not set',$method);
        }

        // If verified, return null
        if ($this->is_verified == 1)
        {
            return null;
        }

        $db->sql_switch('sketchbookcafe');

        // Get poll info
        $sql = 'SELECT id
            FROM forum_polls
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$poll_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Verify
        $poll_id    = isset($row['id']) ? (int) $row['id'] : 0;
        if ($poll_id < 1)
        {
            SBC::devError('Cannot find poll in database',$method);
        }

        // Set as verified
        $this->is_verified = 1;
    }

    // Count Specific Option for Poll
    final public function countOption($poll_id,$poll_option)
    {
        $method = 'PollOrganizer->countOption()';
        $db     = &$this->db;

        if ($poll_id < 1)
        {
            SBC::devError('$poll_id is not set',$method);
        }
        if ($poll_option < 1 || $poll_option > 10)
        {
            SBC::devError('$poll_option is not set',$method);
        }

        // Verify Poll
        $this->verifyPoll($poll_id);

        // Switch
        $db->sql_switch('sketchbookcafe_forums');

        $table_name = 'p'.$poll_id.'l';

        // Count Type (non statement)
        $sql = 'SELECT COUNT(*)
            FROM '.$table_name.'
            WHERE poll_option='.$poll_option;
        $result = $db->sql_query($sql);
        $row    = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);

        // Total
        $total = isset($row[0]) ? (int) $row[0] : 0;

        // Switch
        $db->sql_switch('sketchbookcafe');

        $column = 'vote'.$poll_option;

        // Update Poll Stats
        $sql = 'UPDATE forum_polls
            SET '.$column.'='.$total.'
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$poll_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }

    // Count Total Votes
    final public function countTotalVotes($poll_id)
    {
        $method = 'PollOrganizer->countTotalVotes()';
        $db     = &$this->db;

        // Verify
        $this->verifyPoll($poll_id);

        // Switch
        $db->sql_switch('sketchbookcafe_forums');

        // Count
        $table_name = 'p'.$poll_id.'l';
        $sql = 'SELECT COUNT(*)
            FROM '.$table_name;
        $result = $db->sql_query($sql);
        $row    = $db->sql_fetchrow($result);

        // Total
        $total = isset($row[0]) ? (int) $row[0] : 0;

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Update poll total votes
        $sql = 'UPDATE forum_polls
            SET total_votes=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ii',$total,$poll_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }
}