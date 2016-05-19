<?php
// @author          Kameloh
// @lastUpdated     2016-05-09

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\TableChallenge\TableChallenge as TableChallenge;

class AdminChallengeFixtable
{
    private $challenge_id = 0;

    private $obj_array = [];

    public function __construct(&$obj_array)
    {
        $method = 'AdminChallengeFixtable->__construct()';

        $this->obj_array = &$obj_array;
    }

    // Set Challenge ID
    final public function setId($challenge_id)
    {
        $method = 'AdminChallengeFixtable->setId()';

        // Set
        $this->challenge_id = isset($challenge_id) ? (int) $challenge_id : 0;
        if ($this->challenge_id < 1)
        {
            SBC::devError('$challenge_id is not set',$method);
        }
    }

    // Process
    final public function process()
    {
        $method = 'AdminChallengeFixtable->process()';

        // Initialize
        $db             = &$this->obj_array['db'];
        $User           = &$this->obj_array['User'];
        $challenge_id   = SBC::checkNumber($this->challenge_id,'$this->challenge_id');

        // Open Connection
        $db->open();

        // Admin Required
        $User->admin($db);
        $User->requireAdminFlag('challenges');

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Challenge Info
        $sql = 'SELECT id 
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
            SBC::devError('Could not find challenge in database',$method);
        }

        // Update Tables
        $TableChallenge = new TableChallenge($challenge_id);
        $TableChallenge->checkTables($db);

        // Close Connection
        $db->close();

        // Header
        header('Location: https://www.sketchbook.cafe/admin/manage_challenges/');
        exit;
    }
}