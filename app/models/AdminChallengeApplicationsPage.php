<?php
// @author          Kameloh
// @lastUpdated     2016-05-17

use SketchbookCafe\SBC\SBC as SBC;

class AdminChallengeApplicationsPage
{
    private $obj_array = [];
    private $result = [];
    private $rownum = 0;

    public function __construct(&$obj_array)
    {
        $this->obj_array = &$obj_array;
    }

    // Process
    final public function process()
    {
        $method = 'AdminChallengeApplicationsPage->process()';

        // Initialize
        $db     = &$this->obj_array['db'];
        $User   = &$this->obj_array['User'];

        // Open Connection
        $db->open();

        // Admin Required
        $User->setFrontpage();
        $User->admin($db);
        $User->requireAdminFlag('challenges');

        // Get Applications
        $this->getApplications($db);

        // Process All Data
        $ProcessAllData = new ProcessAllData();

        // Close Connection
        $db->close();
    }

    // Get Challenge Applications
    final private function getApplications(&$db)
    {
        $method = 'AdminChallengeApplicationsPage->getApplications';

        // Initialize
        $Member = &$this->obj_array['Member'];

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get applications
        $sql = 'SELECT id, user_id, points, name, description, requirements
            FROM challenge_applications
            WHERE isdeleted=0
            ORDER BY id
            ASC
            LIMIT 20';
        $this->result   = $db->sql_query($sql);
        $this->rownum   = $db->sql_numrows($this->result);

        // Add vars
        $Member->idAddRows($this->result,'user_id');
    }

    // Get Result
    final public function getResult()
    {
        return $this->result;
    }

    // Get Rownum
    final public function getRownum()
    {
        return $this->rownum;
    }
}