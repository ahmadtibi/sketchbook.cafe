<?php
// @author          Kameloh
// @lastUpdated     2016-05-12

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\ChallengeOrganizer\ChallengeOrganizer as ChallengeOrganizer;

class ChallengeEntryPage
{
    private $entry_id = 0;
    private $challenge_id = 0;

    private $entry_row = [];

    private $obj_array = [];

    // Construct
    public function __construct(&$obj_array)
    {
        $method = 'ChallengeEntryPage->__construct()';

        $this->obj_array = &$obj_array;
    }

    // Set Entry ID
    final public function setEntryId($entry_id)
    {
        $method = 'ChallengeEntryPage->setEntryId()';

        $this->entry_id = isset($entry_id) ? (int) $entry_id : 0;
        if ($this->entry_id < 1)
        {
            SBC::devError('Entry ID is not set',$method);
        }
    }

    // Process
    final public function process()
    {
        $method = 'ChallengeEntryPage->process()';

        // Initialize
        $db         = &$this->obj_array['db'];
        $User       = &$this->obj_array['User'];
        $entry_id   = $this->entry_id;
        if ($entry_id < 1)
        {
            SBC::devError('Entry ID is not set',$method);
        }

        // Open Connection
        $db->open();

        // User Optional + Frontpage
        $User->setFrontpage();
        $User->optional($db);

        // Get Entry Info
        $this->getEntry($db);

        // Get Challenge Info
        $this->getChallenge($db);

        // Process Data
        $ProcessAllData = new ProcessAllData();

        // Close Connection
        $db->close();
    }

    // Get Entry
    final private function getEntry(&$db)
    {
        $method = 'ChallengeEntryPage->getEntry()';

        // Initialize
        $Images     = &$this->obj_array['Images'];
        $Member     = &$this->obj_array['Member'];
        $entry_id   = SBC::checkNumber($this->entry_id,'$this->entry_id');

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Entry Information
        $sql = 'SELECT id, difficulty, challenge_id, comment_id, image_id, user_id, date_created, 
            ispending, isdeleted
            FROM challenge_entries
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$entry_id);
        $this->entry_row = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Verify
        $entry_id   = isset($this->entry_row['id']) ? (int) $this->entry_row['id'] : 0;
        if ($entry_id < 1)
        {
            SBC::userError('Could not find entry in database');
        }
        if ($this->entry_row['isdeleted'] == 1)
        {
            SBC::userError('Entry no longer exists');
        }
        if ($this->entry_row['ispending'] == 1)
        {
            SBC::userError('Entry is pending and cannot be viewed');
        }

        // Set other vars
        $Member->idAddOne($this->entry_row['user_id']);
        $Images->idAddOne($this->entry_row['image_id']);
        $this->challenge_id = $this->entry_row['challenge_id'];
    }

    // Get Challenge Info
    final private function getChallenge(&$db)
    {
        $method = 'ChallengeEntryPage->getChallenge()';

        // Check
        if ($this->challenge_id < 1)
        {
            SBC::devError('Challenge ID is not set',$method);
        }

        // Challenge Organizer
        //$ChallengeOrganizer = new ChallengeOrganizer($db);
        //$this->challenge_row = $ChallengeOrganizer->getChallengeRow($this->challenge_id);
    }
}