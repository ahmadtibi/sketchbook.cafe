<?php
// @author          Kameloh
// @lastUpdated     2016-05-04

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\SBCGetUsername\SBCGetUsername as SBCGetUsername;
use SketchbookCafe\Message\Message as Message;
use SketchbookCafe\TextareaSettings\TextareaSettings as TextareaSettings;
use SketchbookCafe\TableChallenge\TableChallenge as TableChallenge;

class AdminChallengeSubmit
{
    private $id = 0;
    private $rd = 0;
    private $time = 0;
    private $owner_username = '';
    private $owner_user_id = 0;
    private $user_id = 0;
    private $ip_address = '';
    private $points = 0;

    private $name = '';
    private $name_code = '';
    private $description = '';
    private $description_code = '';
    private $requirements = '';
    private $requirements_code = '';

    private $thread_id = 0;
    private $category_id = 0;
    private $challenge_id = 0;

    // Construct
    public function __construct(&$obj_array)
    {
        $method = 'AdminChallengeSubmit->__construct()';

        // Initialize Objects
        $db                 = &$obj_array['db'];
        $User               = &$obj_array['User'];
        $this->time         = SBC::getTime();
        $this->ip_address   = SBC::getIpAddress();
        $this->rd           = SBC::rd();

        // Category ID
        $this->category_id = isset($_POST['category_id']) ? (int) $_POST['category_id'] : 0;
        if ($this->category_id < 1)
        {
            SBC::devError('$category_id is not set',$method);
        }

        // Thread ID
        $this->thread_id = isset($_POST['thread_id']) ? (int) $_POST['thread_id'] : 0;
        if ($this->thread_id < 1)
        {
            SBC::devError('$thread_id is not set',$method);
        }

        // Username
        $this->owner_username = SBCGetUsername::process($_POST['username']);

        // Challenge Name
        $challengeObj   = new Message(array
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
        $challengeObj->insert($_POST['name']);

        // Points
        $this->points = isset($_POST['points']) ? (int) $_POST['points'] : 0;
        if ($this->points < 1)
        {
            SBC::devError('Points is not set',$method);
        }
        if ($this->points > 100)
        {
            SBC::devError('Max points is 100',$method);
        }

        // Textarea Settings
        $TS                     = new TextareaSettings('challenge_description');
        $description_settings   = $TS->getSettings();
        $TS2                    = new TextareaSettings('challenge_requirements');
        $challenge_requirements = $TS2->getSettings();

        // Description
        $DescriptionObj = new Message($description_settings);
        $DescriptionObj->insert($_POST['description']);

        // Requirements
        $RequirementsObj = new Message($challenge_requirements);
        $RequirementsObj->insert($_POST['requirements']);

        // Set
        $this->name                 = $challengeObj->getMessage();
        $this->name_code            = $challengeObj->getMessageCode();
        $this->description          = $DescriptionObj->getMessage();
        $this->description_code     = $DescriptionObj->getMessageCode();
        $this->requirements         = $RequirementsObj->getMessage();
        $this->requirements_code    = $RequirementsObj->getMessageCode();

        // Open Connection
        $db->open();

        // Admin Required + Process Data
        $User->admin($db);
        $User->requireAdminFlag('challenges');
        $this->user_id = $User->getUserId();

        // Get Thread Info
        $this->getThreadInfo($db);

        // Get Category Info
        $this->getCategoryInfo($db);

        // Get Username Info
        $this->getUserInfo($db);

        // Create Challenge
        $this->createChallenge($db);

        // Create Tables
        $this->createTables($db);

        // Update Challenge
        $this->updateChallenge($db);

        // Close Connection
        $db->close();

        // Header
    }

    // Get Thread Info
    final private function getThreadInfo(&$db)
    {
        $method = 'AdminChallengeSubmit->getThreadInfo()';

        $thread_id  = SBC::checkNumber($this->thread_id,'$this->thread_id');

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Forum Thread Info
        $sql = 'SELECT id, challenge_id, isdeleted
            FROM forum_threads
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$thread_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Verify
        $thread_id = isset($row['id']) ? (int) $row['id'] : 0;
        if ($thread_id < 1)
        {
            SBC::devError('Could not find thread in database',$method);
        }

        // Deleted?
        if ($row['isdeleted'] == 1)
        {
            SBC::devError('Thread no longer exists',$method);
        }

        // Does the thread already have a challenge ID set?
        if ($row['challenge_id'] > 0)
        {
            SBC::devError('Thread already has a challenge set('.$row['challenge_id'].')',$method);
        }
    }

    // Get Category Info
    final private function getCategoryInfo(&$db)
    {
        $method = 'AdminChallengeSubmit->getCategoryInfo()';

        // Initialize
        $category_id    = SBC::checkNumber($this->category_id,'$this->category_id');

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Category Info
        $sql = 'SELECT id, isdeleted
            FROM challenge_categories
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$category_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Verify
        $category_id    = isset($row['id']) ? (int) $row['id'] : 0;
        if ($category_id < 1)
        {
            SBC::devError('Could not find category in database',$method);
        }

        // Deleted?
        if ($row['isdeleted'] == 1)
        {
            SBC::devError('Category is deleted',$method);
        }
    }

    // Get User Info
    final private function getUserInfo(&$db)
    {
        $method = 'AdminChallengeSubmit->getUserInfo()';

        // Initialize
        $owner_username = SBC::checkEmpty($this->owner_username,'$this->owner_username');

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get User Info
        $sql = 'SELECT id
            FROM users
            WHERE username=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('s',$owner_username);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Id?
        $id = isset($row['id']) ? (int) $row['id'] : 0;
        if ($id < 1)
        {
            SBC::devError('Could not find user('.$owner_username.') in database',$method);
        }

        // Set
        $this->owner_user_id = $id;
    }

    // Create Challenge
    final private function createChallenge(&$db)
    {
        $method = 'AdminChallengeSubmit->createChallenge()';

        // Initialize
        $rd                     = $this->rd;
        $time                   = $this->time;
        $ip_address             = $this->ip_address;
        $user_id                = $this->user_id;
        $points                 = SBC::checkNumber($this->points,'$this->points');
        $owner_user_id          = SBC::checkNumber($this->owner_user_id,'$this->owner_user_id');
        $name                   = SBC::checkEmpty($this->name,'$this->name');
        $name_code              = SBC::checkEmpty($this->name_code,'$this->name_code');
        $description            = SBC::checkEmpty($this->description,'$this->description');
        $description_code       = SBC::checkEmpty($this->description_code,'$this->description_code');
        $requirements           = SBC::checkEmpty($this->requirements,'$this->requirements');
        $requirements_code      = SBC::checkEmpty($this->requirements_code,'$this->requirements_code');

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Insert Challenge
        $sql = 'INSERT INTO challenges
            SET rd=?,
            user_id=?,
            owner_user_id=?,
            date_created=?,
            date_updated=?,
            ip_created=?,
            ip_updated=?,
            points=?,
            name=?,
            name_code=?,
            description=?,
            description_code=?,
            requirements=?,
            requirements_code=?,
            isdeleted=1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('iiiiississssss',$rd,$user_id,$owner_user_id,$time,$time,$ip_address,$ip_address,$points,$name,$name_code,$description,$description_code,$requirements,$requirements_code);
        SBC::statementExecute($stmt,$db,$sql,$method);

        // Get Challenge ID
        $sql = 'SELECT id
            FROM challenges
            WHERE rd=?
            AND user_id=?
            AND owner_user_id=?
            AND date_created=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('iiii',$rd,$user_id,$owner_user_id,$time);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Set
        $challenge_id   = isset($row['id']) ? (int) $row['id'] : 0;
        if ($challenge_id < 1)
        {
            SBC::devError('Could not insert challenge into database',$method);
        }
        $this->challenge_id = $challenge_id;
    }

    // Create Tables
    final private function createTables(&$db)
    {
        $method = 'AdminChallengeSubmit->createTables()';

        // Initialize
        $challenge_id = SBC::checkNumber($this->challenge_id,'$this->challenge_id');

        // Create Table
        $TableChallenge = new TableChallenge($challenge_id);
        $TableChallenge->checkTables($db);
    }

    // Update challenge
    final private function updateChallenge(&$db)
    {
        $method = 'AdminChallengeSubmit->updateChallenge()';

        // Initialize
        $thread_id      = SBC::checkNumber($this->thread_id,'$this->thread_id');
        $challenge_id   = SBC::checkNumber($this->challenge_id,'$this->challenge_id');
        $category_id    = SBC::checkNumber($this->category_id,'$this->category_id');

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Update challenge as not deleted
        $sql = 'UPDATE challenges
            SET category_id=?,
            thread_id=?,
            isdeleted=0
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('iii',$category_id,$thread_id,$challenge_id);
        SBC::statementExecute($stmt,$db,$sql,$method);

        // Update Thread
        $sql = 'UPDATE forum_threads
            SET challenge_id=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ii',$challenge_id,$thread_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }
}