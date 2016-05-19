<?php
// @author          Kameloh
// @lastUpdated     2016-05-12

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\SBCChallenges\SBCChallenges as SBCChallenges;

class AdminPendingEntriesPage
{
    private $entries_result = [];
    private $entries_rownum = 0;

    private $challenge_row = [];

    private $obj_array = [];

    // Construct
    public function __construct(&$obj_array)
    {
        $method = 'AdminPendingEntriesPage->__construct()';

        $this->obj_array = &$obj_array;

        // Initialize
        $db         = &$obj_array['db'];
        $User       = &$obj_array['User'];
        $Comment    = &$obj_array['Comment'];
        $Images     = &$obj_array['Images'];

        // Open Connection
        $db->open();

        // Admin Required
        $User->setFrontpage();
        $User->admin($db);
        $User->requireAdminFlag('approve_entries');

        // Get Pending Entries
        $this->getPendingEntries($db);

        // Get Challenges
        $SBCChallenges  = new SBCChallenges($this->obj_array);
        $SBCChallenges->idAddRows($this->entries_result,'challenge_id');
        $SBCChallenges->process();
        $this->challenge_row = $SBCChallenges->getChallengeRow();

        // Process Stuff
        $Comment->idAddRows($this->entries_result,'comment_id');
        $Images->idAddRows($this->entries_result,'image_id');
        $ProcessAllData = new ProcessAllData();

        // Close Connection
        $db->close();
    }

    // Get Pending Entries
    final private function getPendingEntries(&$db)
    {
        $method = 'AdminPendingEntriesPage->getPendingEntries()';

        // Initialize
        $db     = &$this->obj_array['db'];
        $Member = &$this->obj_array['Member'];
        $Images = &$this->obj_array['Images'];

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Pending Entries
        $sql = 'SELECT id, difficulty, challenge_id, comment_id, image_id, user_id, date_created
            FROM challenge_entries
            WHERE ispending=1
            AND isdeleted=0
            ORDER BY id
            DESC
            LIMIT 20';
        $this->entries_result   = $db->sql_query($sql);
        $this->entries_rownum   = $db->sql_numrows($this->entries_result);
    }

    // Get Challenge Row
    final public function getChallengeRow()
    {
        return $this->challenge_row;
    }

    // Get Entries Result
    final public function getEntriesResult()
    {
        return $this->entries_result;
    }

    // Get Entries Rownum
    final public function getEntriesRownum()
    {
        return $this->entries_rownum;
    }
}