<?php
// @author          Kameloh
// @lastUpdated     2016-05-09
namespace SketchbookCafe\Entry;

use SketchbookCafe\SBC\SBC as SBC;

class Entry
{
    private $entry_id = 0;
    private $entry_data = [];

    private $verified = [];

    private $db;

    // Construct
    public function __construct(&$db)
    {
        $method = 'Entry->__construct()';

        $this->db   = &$db;
    }

    // Verify Entry
    final private function verifyEntry($entry_id)
    {
        $method = 'Entry->verifyEntry()';

        // Is this entry already verified?
        if (isset($this->verified[$entry_id]))
        {
            if ($this->verified[$entry_id] == 1)
            {
                return null;
            }
        }

        // Is entry ID set?
        if ($entry_id < 1)
        {
            SBC::devError('$entry_id is not set',$method);
        }

        // Initialize
        $db = &$this->db;

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get entry information
        $sql = 'SELECT id, difficulty, challenge_id, comment_id, image_id, user_id, date_created, 
            isnew, ispending, isdeleted
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

        // Set Data
        $this->entry_data[$entry_id] = $row;

        // Set as Verified
        $this->verified[$entry_id] = 1;
    }

    // Get Entry Info
    final public function getEntryRow($entry_id)
    {
        $method = 'Entry->getEntry()';

        // Verify Entry
        $this->verifyEntry($entry_id);

        return $this->entry_data[$entry_id];
    }
}