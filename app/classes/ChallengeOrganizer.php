<?php
// @author          Kameloh
// @lastUpdated     2016-05-04
namespace SketchbookCafe\ChallengeOrganizer;

use SketchbookCafe\SBC\SBC as SBC;

class ChallengeOrganizer
{
    private $db;
    private $ip_address = '';
    private $time = 0;
    private $rd = 0;

    // Construct
    public function __construct(&$db)
    {
        $this->db           = &$db;
        $this->ip_address   = SBC::getIpAddress();
        $this->time         = SBC::getTime();
        $this->rd           = SBC::rd();
    }

    // Get New Entry
    final public function getNewEntry($challenge_id,$comment_id,$image_id,$user_id)
    {
        $method = 'ChallengeOrganizer->getNewEntry()';

        // Initialize
        $db             = &$this->db;
        $ip_address     = $this->ip_address;
        $rd             = $this->rd;
        $time           = $this->time;

        // Check values
        if ($challenge_id < 1)
        {
            SBC::devError('$challenge_id is not set',$method);
        }
        if ($comment_id < 1)
        {
            SBC::devError('$comment_id is not set',$method);
        }
        if ($image_id < 1)
        {
            SBC::devError('$image_id is not set',$method);
        }
        if ($user_id < 1)
        {
            SBC::devError('$user_id is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Insert new entry
        $sql = 'INSERT INTO challenge_entries
            SET rd=?,
            challenge_id=?,
            comment_id=?,
            image_id=?,
            user_id=?,
            date_created=?,
            ip_created=?,
            isnew=1,
            isdeleted=1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('iiiiiis',$rd,$challenge_id,$comment_id,$image_id,$user_id,$time,$ip_address);
        SBC::statementExecute($stmt,$db,$sql,$method);

        // Get Entry ID
        $sql = 'SELECT id
            FROM challenge_entries
            WHERE rd=?
            AND challenge_id=?
            AND comment_id=?
            AND user_id=?
            AND date_created=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('iiiii',$rd,$challenge_id,$comment_id,$user_id,$time);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Verify
        $entry_id = isset($row['id']) ? (int) $row['id'] : 0;
        if ($entry_id < 1)
        {
            SBC::devError('Could not insert new entry into database',$method);
        }

        // Update Entry as not deleted
        $sql = 'UPDATE challenge_entries
            SET isdeleted=0
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$entry_id);
        SBC::statementExecute($stmt,$db,$sql,$method);

        return $entry_id;
    }
}