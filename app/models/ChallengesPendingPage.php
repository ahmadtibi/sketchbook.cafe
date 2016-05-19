<?php
// @author          Kameloh
// @lastUpdated     2016-05-19

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\TextareaSettings\TextareaSettings as TextareaSettings;
use SketchbookCafe\Form\Form as Form;

class ChallengesPendingPage
{
    private $is_admin = 0;
    private $user_id = 0;
    private $app_id = 0;
    private $app_row = [];
    private $ChallengeForm;
    private $AdminForm;

    private $points = 0;
    private $name_code = '';
    private $description_code = '';
    private $requirements_code = '';

    private $obj_array = [];

    public function __construct(&$obj_array)
    {
        $this->obj_array = &$obj_array;
    }

    // Set Application ID
    final public function setApplicationId($app_id)
    {
        $method = 'ChallengesPendingPage->setApplicationId()';

        $this->app_id = isset($app_id) ? (int) $app_id : 0;
        if ($this->app_id < 1)
        {
            SBC::devError('Application ID is not set',$method);
        }
    }

    // Process
    final public function process()
    {
        $method = 'ChallengesPendingPage->process()';

        // Initialize
        $db             = &$this->obj_array['db'];
        $User           = &$this->obj_array['User'];

        // Open Connection
        $db->open();

        // User Required
        $User->setFrontpage();
        $User->required($db);
        $this->user_id = $User->getUserId();
        if ($User->isAdmin())
        {
            $this->is_admin = 1;
        }

        // Get Application Info
        $this->getAppInfo($db);

        // Process Data
        $ProcessAllData = new ProcessAllData();

        // Close Connection
        $db->close();

        // Create Edit Form
        $this->createEditForm();

        // Admin Form
        if ($User->isAdmin())
        {
            $this->createAdminForm();
        }
    }

    // Get Application Info
    final private function getAppInfo(&$db)
    {
        $method = 'ChallengesPendingPage->getAppInfo()';

        // Initialize
        $Member         = &$this->obj_array['Member'];
        $app_id         = $this->app_id;
        $user_id        = $this->user_id;
        $is_admin       = $this->is_admin;
        if ($app_id < 1)
        {
            SBC::devError('Application ID is not set',$method);
        }
        if ($user_id < 1)
        {
            SBC::devError('User ID is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Info
        $sql = 'SELECT id, user_id, date_created, points, name, name_code, 
            description, description_code, requirements, requirements_code, isdeleted
            FROM challenge_applications
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$app_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Set?
        $app_id = isset($row['id']) ? (int) $row['id'] : 0;
        if ($app_id < 1)
        {
            SBC::userError('Could not find application in database');
        }

        // Deleted?
        if ($row['isdeleted'] == 1)
        {
            SBC::userError('Application no longer exists');
        }

        // Owner or Admin Only
        if ($user_id != $row['user_id'])
        {
            if ($is_admin != 1)
            {
                SBC::userError('Sorry, you may only view applications that belong to you');
            }
        }

        // Add
        $Member->idAddOne($row['user_id']);

        // Set
        $this->points               = $row['points'];
        $this->name_code            = $row['name_code'];
        $this->description_code     = $row['description_code'];
        $this->requirements_code    = $row['requirements_code'];
        $this->app_row              = $row;
    }

    // Get App Row
    final public function getAppRow()
    {
        return $this->app_row;
    }

    // Create Edit Form
    final private function createEditForm()
    {
        $method = 'ChallengesPendingPage->createEditForm()';

        $method = 'ChallengesPage->createChallengeForm()';

        // New Form
        $ChallengeForm = new Form(array
        (
            'name'      => 'editpending',
            'action'    => 'https://www.sketchbook.cafe/challenges/pending_edit/',
            'method'    => 'POST',
        ));

        // Application ID
        $ChallengeForm->field['app_id'] = $ChallengeForm->hidden(array
        (
            'name'  => 'app_id',
            'value' => $this->app_id,
        ));

        // Description
        $ts_description_obj = new TextareaSettings('challenge_description');
        $ts_description_obj->setValue($this->description_code);
        $ChallengeForm->field['description'] = $ChallengeForm->textarea($ts_description_obj->getSettings());

        // Requirements
        $ts_requirements_obj = new TextareaSettings('challenge_requirements');
        $ts_requirements_obj->setValue($this->requirements_code);
        $ChallengeForm->field['requirements'] = $ChallengeForm->textarea($ts_requirements_obj->getSettings());

        // Name
        $ChallengeForm->field['title']  = $ChallengeForm->input(array
        (
            'name'          => 'name',
            'type'          => 'text',
            'max'           => 100,
            'value'         => $this->name_code,
            'placeholder'   => 'challenge title',
            'css'           => 'input300',
        ));

        // Points
        $ChallengeForm->field['points'] = $ChallengeForm->input(array
        (
            'name'          => 'points',
            'type'          => 'text',
            'max'           => 3,
            'value'         => $this->points,
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

    // Get Form
    final public function getEditForm()
    {
        return $this->ChallengeForm;
    }

    // Create Admin Form
    final private function createAdminForm()
    {
        $method = 'ChallengesPendingPage->createAdminForm()';

        // New Form
        $AdminForm = new Form(array
        (
            'name'      => 'adminpendingform',
            'action'    => 'https://www.sketchbook.cafe/challenges/pending_admin/',
            'method'    => 'POST',
        ));

        // Application ID
        $AdminForm->field['app_id'] = $AdminForm->hidden(array
        (
            'name'  => 'app_id',
            'value' => $this->app_id,
        ));

        // Select
        $list[' ']          = 0;
        $list['Approve']    = 1;
        $list['Delete']     = 2;
        $input = array('name'=>'action');
        $value = 0;
        $AdminForm->field['action'] = $AdminForm->dropdown($input,$list,$value);

        // Confirm
        $AdminForm->field['confirm'] = $AdminForm->checkbox(array
        (
            'name'      => 'confirm',
            'value'     => 1,
            'checked'   => 0,
            'css'       => '',
        ));

        // Submit
        $AdminForm->field['submit'] = $AdminForm->submit(array
        (
            'name'      => 'Submit',
            'css'       => '',
        ));

        // Set
        $this->AdminForm = $AdminForm;
    }

    // Get Admin Form
    final public function getAdminForm()
    {
        return $this->AdminForm;
    }
}