<?php
// @author          Kameloh
// @lastUpdated     2016-05-19

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\TextareaSettings\TextareaSettings as TextareaSettings;
use SketchbookCafe\Message\Message as Message;
use SketchbookCafe\MailboxRobot\MailboxRobot as MailboxRobot;

class ChallengesApplySubmit
{
    private $points_requirement = 100; // number of points needed for the user to apply

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

    private $mail_id = 0;

    private $app_id = 0;

    // Construct
    public function __construct(&$obj_array)
    {
        $method = 'ChallengesApplySubmit->__construct()';

        // Initialize
        $db                 = &$obj_array['db'];
        $User               = &$obj_array['User'];
        $this->time         = SBC::getTime();
        $this->ip_address   = SBC::getIpAddress();
        $this->rd           = SBC::rd();

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

        // Get User Info
        $this->getUserInfo($db);

        // Check if they already have a challenge pending
        $this->checkChallenge($db);

        // Create Challenge Application
        $this->createChallengeApplication($db);

        // Create Mailbox Notification
        $this->createMailNotification($db);

        // Update Application Info
        $this->updateApplication($db);

        // Close Connection
        $db->close();

        // Header
        header('Location: https://www.sketchbook.cafe/mailbox/note/'.$this->mail_id.'/');
        exit;
    }

    // Get User Info
    final private function getUserInfo(&$db)
    {
        $method = 'ChallengesApplySubmit->getUserInfo()';

        $user_id    = $this->user_id;
        if ($user_id < 1)
        {
            SBC::devError('User ID is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get user points
        $sql = 'SELECT sketch_points
            FROM users
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$user_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Points
        $sketch_points = isset($row['sketch_points']) ? (int) $row['sketch_points'] : 0;
        if ($sketch_points < $this->points_requirement)
        {
            SBC::userError('Sorry, you need at least '.$this->points_requirement.' 
                sketch points to create a challenge (you currently have '.$sketch_points.')');
        }
    }

    // Check Challenge Pending
    final private function checkChallenge(&$db)
    {
        $method = 'ChallengesApplySubmit->checkChallenge()';

        $user_id    = $this->user_id;
        if ($user_id < 1)
        {
            SBC::devError('User ID is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Find something
        $sql = 'SELECT id
            FROM challenge_applications
            WHERE user_id=?
            AND isdeleted=0
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$user_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // ID?
        $id = isset($row['id']) ? (int) $row['id'] : 0;
        if ($id > 0)
        {
            SBC::userError('Sorry, you currently have a pending application (ID#'.$id.')');
        }
    }

    // Create Challenge Application
    final private function createChallengeApplication(&$db)
    {
        $method = 'ChallengesApplySubmit->createChallengeApplication()';

        // Initialize
        $rd                 = $this->rd;
        $time               = $this->time;
        $ip_address         = $this->ip_address;
        $user_id            = SBC::checkNumber($this->user_id,'$this->user_id');

        $points             = SBC::checkNumber($this->points,'$this->points');
        $name               = SBC::checkEmpty($this->name,'$this->name');
        $name_code          = SBC::checkEmpty($this->name_code,'$this->name_code');
        $description        = SBC::checkEmpty($this->description,'$this->description');
        $description_code   = SBC::checkEmpty($this->description_code,'$this->description_code');
        $requirements       = SBC::checkEmpty($this->requirements,'$this->requirements');
        $requirements_code  = SBC::checkEmpty($this->requirements_code,'$this->requirements_code');

        // Switch
        $db->sql_Switch('sketchbookcafe');

        // Insert application
        $sql = 'INSERT INTO challenge_applications
            SET rd=?,
            user_id=?,
            date_created=?,
            ip_created=?,
            points=?,
            name=?,
            name_code=?,
            description=?,
            description_code=?,
            requirements=?,
            requirements_code=?,
            isdeleted=1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('iiisissssss',$rd,$user_id,$time,$ip_address,$points,$name,$name_code,$description,$description_code,$requirements,$requirements_code);
        SBC::statementExecute($stmt,$db,$sql,$method);

        // Get ID
        $sql = 'SELECT id
            FROM challenge_applications
            WHERE rd=?
            AND user_id=?
            AND date_created=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('iii',$rd,$user_id,$time);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // ID?
        $app_id = isset($row['id']) ? (int) $row['id'] : 0;
        if ($app_id < 1)
        {
            SBC::devError('Could not insert new application into database',$method);
        }

        // Update challenge
        $sql = 'UPDATE challenge_applications
            SET isdeleted=0
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$app_id);
        SBC::statementExecute($stmt,$db,$sql,$method);

        // Set
        $this->app_id = $app_id;
    }

    // Create Mail Notification and Update Timers
    final private function createMailNotification(&$db)
    {
        $method = 'ChallengesApplySubmit->createMailNotification()';

        // Initialize
        $user_id    = $this->user_id;
        $app_id     = $this->app_id;
        if ($user_id < 1)
        {
            SBC::devError('User ID is not set',$method);
        }
        if ($app_id < 1)
        {
            SBC::devError('Application ID is not set',$method);
        }

        // NL2BR Enabled
        $message = '
            Thank you for submitting a challenge! You can view your application here:
            <br/>https://www.sketchbook.cafe/challenges/pending/'.$app_id.'/
            <br/>
            <br/>FIXME';

        // Mailbox Robot
        $MailboxRobot = new MailboxRobot($db);
        $MailboxRobot->setUserId($user_id);
        $MailboxRobot->setTitle('Challenge Application ID#'.$app_id);
        $MailboxRobot->setMessage($message);
        $MailboxRobot->createMail();
        $this->mail_id = $MailboxRobot->getMailId();
    }

    // Update App Info
    final private function updateApplication(&$db)
    {
        $method = 'ChallengesApplySubmit->updateChallenge()';

        // Initialize
        $app_id     = $this->app_id;
        $mail_id    = $this->mail_id;
        if ($app_id < 1)
        {
            SBC::devError('Application ID is not set',$method);
        }
        if ($mail_id < 1)
        {
            SBC::devError('Mail ID is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Update application 
        $sql = 'UPDATE challenge_applications
            SET mail_id=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ii',$mail_id,$app_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }
}