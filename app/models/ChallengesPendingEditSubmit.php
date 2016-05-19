<?php
// @author          Kameloh
// @lastUpdated     2016-05-19

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\TextareaSettings\TextareaSettings as TextareaSettings;
use SketchbookCafe\Message\Message as Message;

class ChallengesPendingEditSubmit
{
    private $app_id = 0;

    private $is_admin = 0;
    private $user_id = 0;
    private $ip_address = '';
    private $time = 0;
    private $rd = 0;

    private $points = 0;
    private $name = '';
    private $name_code = '';
    private $description = '';
    private $description_code = '';
    private $requirements = '';
    private $requirements_code = '';

    private $obj_array = [];

    public function __construct(&$obj_array)
    {
        $method = 'ChallengesPendingEditSubmit->__construct()';

        // Initialize
        $db                 = &$obj_array['db'];
        $User               = &$obj_array['User'];
        $this->time         = SBC::getTime();
        $this->ip_address   = SBC::getIpAddress();
        $this->rd           = SBC::rd();
        $this->app_id       = isset($_POST['app_id']) ? (int) $_POST['app_id'] : 0;
        if ($this->app_id < 1)
        {
            SBC::devError('Application ID is not set',$method);
        }

        // Textarea Settings
        $ts_description_obj     = new TextareaSettings('challenge_description');
        $ts_description         = $ts_description_obj->getSettings();
        $ts_requirements_obj    = new TextareaSettings('challenge_requirements');
        $ts_requirements        = $ts_requirements_obj->getSettings();

        // Challenge Name
        $nameObj    = new Message(array
        (
            'name'          => 'name',
            'min'           => 1,
            'column_max'    => 250,
            'nl2br'         => 0,
            'basic'         => 0,
            'ajax'          => 0,
            'images'        => 0,
            'videos'        => 0,
        ));
        $nameObj->insert($_POST['name']);

        // Points
        $this->points   = isset($_POST['points']) ? (int) $_POST['points'] : 0;
        if ($this->points < 1)
        {
            SBC::userError('Points is not set');
        }
        if ($this->points > 100)
        {
            SBC::userError('Max points is 100');
        }

        // Description and Requirements
        $descObj    = new Message($ts_description);
        $descObj->insert($_POST['description']);
        $requireObj = new Message($ts_requirements);
        $requireObj->insert($_POST['requirements']);

        // Set vars?
        $this->name                 = $nameObj->getMessage();
        $this->name_code            = $nameObj->getMessageCode();
        $this->description          = $descObj->getMessage();
        $this->description_code     = $descObj->getMessageCode();
        $this->requirements         = $requireObj->getMessage();
        $this->requirements_code    = $requireObj->getMessageCode();

        // Open Connection
        $db->open();

        // User Required
        $User->required($db);
        $this->user_id = $User->getUserId();
        if ($User->isAdmin())
        {
            $this->is_admin = 1;
        }

        // Get App Info
        $this->getAppInfo($db);

        // Update App ID
        $this->updateApp($db);

        // Close Connection
        $db->close();

        // Header
        header('Location: https://www.sketchbook.cafe/challenges/pending/'.$this->app_id.'/');
        exit;
    }

    // Get Application Info
    final private function getAppInfo(&$db)
    {
        $method = 'ChallengesPendingEditSubmit->getAppInfo()';

        // Initialize
        $app_id     = $this->app_id;
        $user_id    = $this->user_id;
        $is_admin   = $this->is_admin;
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
        $sql = 'SELECT id, user_id, isdeleted
            FROM challenge_applications
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$app_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Verify?
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
                SBC::userError('Sorry, you may only edit applications that belong to you');
            }
        }
    }

    // Update Application
    final private function updateApp(&$db)
    {
        $method = 'ChallengesPendingEditSubmit->updateApp()';

        // Initialize
        $app_id             = $this->app_id;
        $points             = SBC::checkNumber($this->points,'$this->points');
        $name               = SBC::checkEmpty($this->name,'$this->name');
        $name_code          = SBC::checkEmpty($this->name_code,'$this->name_code');
        $description        = SBC::checkEmpty($this->description,'$this->description');
        $description_code   = SBC::checkEmpty($this->description_code,'$this->description_code');
        $requirements       = SBC::checkEmpty($this->requirements,'$this->requirements');
        $requirements_code  = SBC::checkEmpty($this->requirements_code,'$this->requirements_code');
        if ($app_id < 1)
        {
            SBC::devError('Application ID is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Update
        $sql = 'UPDATE challenge_applications
            SET points=?,
            name=?,
            name_code=?,
            description=?,
            description_code=?,
            requirements=?,
            requirements_code=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('issssssi',$points,$name,$name_code,$description,$description_code,$requirements,$requirements_code,$app_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }
}