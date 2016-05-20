<?php
// @author          Kameloh
// @lastUpdated     2016-05-19

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\Form\Form as Form;
use SketchbookCafe\TextareaSettings\TextareaSettings as TextareaSettings;

class ChallengesPage
{
    // Pending App for User
    private $user_id = 0;
    private $app_id = 0;

    private $obj_array = [];
    private $result = [];
    private $rownum = 0;

    private $galleries_result = [];
    private $galleries_rownum = [];

    private $applications_result = [];
    private $applications_rownum = 0;

    private $ChallengeForm;

    // Construct
    public function __construct(&$obj_array)
    {
        $this->obj_array = &$obj_array;
    }

    // Process
    final public function process()
    {
        $method = 'ChallengesPage->process()';

        // Initialize
        $db     = &$this->obj_array['db'];
        $User   = &$this->obj_array['User'];

        // Open Connection
        $db->open();

        // User Optional + Frontpage
        $User->setFrontpage();
        $User->optional($db);

        // Get Challenges
        $this->getChallenges($db);

        if ($User->loggedIn())
        {
            $this->user_id = $User->getUserId();
            $this->getUserPending($db);
        }

        if ($User->isAdmin())
        {
            $this->getPending($db);
        }

        // Process Data
        $ProcessAllData = new ProcessAllData();

        // Close Connection
        $db->close();

        // Create New Challenge Form
        if ($User->getUserId() > 0)
        {
            $this->createChallengeForm();
        }
    }

    // Get Challenges
    final private function getChallenges(&$db)
    {
        $method = 'ChallengesPage->getChallenges()';

        // Initialize
        $Member = &$this->obj_array['Member'];
        $Images = &$this->obj_array['Images'];

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Challenges
        $sql = 'SELECT id, category_id, thread_id, owner_user_id, date_created, date_updated,
            points, name, description, requirements, images_array 
            FROM challenges
            WHERE isdeleted=0
            ORDER BY id
            DESC
            LIMIT 50';
        $this->result   = $db->sql_query($sql);
        $this->rownum   = $db->sql_numrows($this->result);

        // Add Users
        $Member->idAddRows($this->result,'owner_user_id');
        $Images->addStringByResult($this->result,'images_array');
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

    // Create form
    final private function createChallengeForm()
    {
        $method = 'ChallengesPage->createChallengeForm()';

        // Textarea Settings
        $ts_description_obj     = new TextareaSettings('challenge_description');
        $ts_description = $ts_description_obj->getSettings();
        $ts_requirements_obj    = new TextareaSettings('challenge_requirements');
        $ts_requirements = $ts_requirements_obj->getSettings();

        // New Form
        $ChallengeForm = new Form(array
        (
            'name'      => 'applyforchallenge',
            'action'    => 'https://www.sketchbook.cafe/challenges/apply/',
            'method'    => 'POST',
        ));

        // Description and Requirements
        $ChallengeForm->field['description'] = $ChallengeForm->textarea($ts_description);
        $ChallengeForm->field['requirements'] = $ChallengeForm->textarea($ts_requirements);

        // Name
        $ChallengeForm->field['title']  = $ChallengeForm->input(array
        (
            'name'          => 'name',
            'type'          => 'text',
            'max'           => 100,
            'value'         => '',
            'placeholder'   => 'challenge title',
            'css'           => 'input300',
        ));

        // Points
        $ChallengeForm->field['points'] = $ChallengeForm->input(array
        (
            'name'          => 'points',
            'type'          => 'text',
            'max'           => 3,
            'value'         => '',
            'placeholder'   => '1-100',
            'css'           => 'input300',
        ));

        // Submit
        $ChallengeForm->field['submit'] = $ChallengeForm->submit(array
        (
            'name'      => 'Submit',
            'css'       => '',
        ));

        // Set
        $this->ChallengeForm = $ChallengeForm;
    }

    // Get Challenge Form
    final public function getChallengeForm()
    {
        return $this->ChallengeForm;
    }

    // Get Pending Applications
    final private function getPending(&$db)
    {
        $method = 'ChallengesPage->getPending()';

        // Initialize
        $Member = &$this->obj_array['Member'];

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Challenges
        $sql = 'SELECT id, user_id, date_created, points, name, description, requirements
            FROM challenge_applications 
            WHERE isdeleted=0
            ORDER BY id
            ASC
            LIMIT 5';
        $this->applications_result  = $db->sql_query($sql);
        $this->applications_rownum  = $db->sql_numrows($this->applications_result);

        // Add members
        $Member->idAddRows($this->applications_result,'user_id');
    }

    // Get Applications Result
    final public function getApplicationsResult()
    {
        return $this->applications_result;
    }

    // Get Applications Rownum
    final public function getApplicationsRownum()
    {
        return $this->applications_rownum;
    }

    // Get User Pending Application
    final public function getUserPending(&$db)
    {
        $method = 'ChallengesPages->getUserPending()';

        // Initialize
        $user_id    = $this->user_id;
        if ($user_id < 1)
        {
            return null;
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Application
        $sql = 'SELECT id
            FROM challenge_applications
            WHERE user_id=?
            AND isdeleted=0
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$user_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Pending app?
        $this->app_id   = isset($row['id']) ? (int) $row['id'] : 0;
    }

    // Get App Id
    final public function getAppId()
    {
        return $this->app_id;
    }
}