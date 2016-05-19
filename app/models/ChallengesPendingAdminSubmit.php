<?php
// @author          Kameloh
// @lastUpdated     2016-05-19

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\ForumThreadRobot\ForumThreadRobot as ForumThreadRobot;
use SketchbookCafe\TableChallenge\TableChallenge as TableChallenge;
use SketchbookCafe\MailboxRobot\MailboxRobot as MailboxRobot;

class ChallengesPendingAdminSubmit
{
    private $rd = 0;
    private $time = 0;
    private $ip_address = 0;

    private $user_id = 0;

    private $mail_id = 0;
    private $app_row = [];
    private $app_id = 0;
    private $app_user_id = 0;
    private $thread_id = 0;
    private $challenge_id = 0;

    public function __construct(&$obj_array)
    {
        $method = 'ChallengesPendingAdminSubmit->__construct()';

        // Initialize Objects
        $this->rd           = SBC::rd();
        $this->time         = SBC::getTime();
        $this->ip_address   = SBC::getIpAddress();
        $db                 = &$obj_array['db'];
        $User               = &$obj_array['User'];
        $this->app_id       = isset($_POST['app_id']) ? (int) $_POST['app_id'] : 0;
        if ($this->app_id < 1)
        {
            SBC::devError('Application ID is not set',$method);
        }

        // Action
        $action = isset($_POST['action']) ? (int) $_POST['action'] : 0;
        if ($action < 1 || $action > 2)
        {
            SBC::userError('Please select an action');
        }

        // Confirm
        $confirm = isset($_POST['confirm']) ? (int) $_POST['confirm'] : 0;
        if ($confirm != 1)
        {
            SBC::userError('You must confirm action to continue');
        }

        // Open Connection
        $db->open();

        // Admin Required
        $User->admin($db);
        $User->requireAdminFlag('challenges');
        $this->user_id = $User->getUserId();

        // Get Application Info
        $this->getAppInfo($db);

        // Accepted?
        if ($action == 1)
        {
            // Create Forum Thread
            $this->createForumThread($db);

            // Create Challenge
            $this->createChallenge($db);

            // Mark challenge application as deleted

            // Mail User
            $this->mailUser($db);
        }
        else
        {
            error('oh noes dont delete maybe');
        }

        // Close Connection
        $db->close();

        error('end of page thingy');
    }

    // Get Application Info
    final private function getAppInfo(&$db)
    {
        $method = 'ChallengesPendingAdminSubmit->getAppInfo()';

        // Initialize
        $app_id = $this->app_id;
        if ($app_id < 1)
        {
            SBC::devError('Application ID is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get info
        $sql = 'SELECT id, mail_id, user_id, points, name, name_code, description, description_code, 
            requirements, requirements_code, isdeleted
            FROM challenge_applications
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$app_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Verify
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

        // Set
        $this->app_user_id  = $row['user_id'];
        $this->app_row      = $row;
        $this->mail_id      = $row['mail_id'];
    }

    // Create Forum Thread
    final private function createForumThread(&$db)
    {
        $method = 'ChallengesPendingAdminSubmit->createForumThread()';

        // Initialize
        $forum_id       = 11; // main challenge forum
        $app_user_id    = $this->app_user_id;
        $name           = $this->app_row['name'];
        $description    = $this->app_row['description'];
        $requirements   = $this->app_row['requirements'];
        if ($app_user_id < 1)
        {
            SBC::devError('Application User ID is not set',$method);
        }
        if (empty($name))
        {
            SBC::devError('Name is not set',$method);
        }
        if (empty($description))
        {
            SBC::devError('Description is not set',$method);
        }
        if (empty($requirements))
        {
            SBC::devError('Requirements is not set',$method);
        }

        // Message
        $message = '<b>Description:</b>
'.$description.'

<b>Requirements:</b>
'.$requirements;


        // Forum Thread
        $ForumThreadRobot = new ForumThreadRobot($db);
        $ForumThreadRobot->setUserId($app_user_id);
        $ForumThreadRobot->setForumId($forum_id);
        $ForumThreadRobot->setTitle($name);
        $ForumThreadRobot->setMessage($message);
        $ForumThreadRobot->process();
        $this->thread_id = $ForumThreadRobot->getThreadId();
    }

    // Create Challenge
    final private function createChallenge(&$db)
    {
        $method = 'ChallengesPendingAdminSubmit->createChallenge()';

        // Initialize
        $rd                 = $this->rd;
        $time               = $this->time;
        $ip_address         = $this->ip_address;
        $thread_id          = $this->thread_id;
        $user_id            = $this->user_id;
        $owner_user_id      = $this->app_user_id;
        $points             = $this->app_row['points'];
        $name               = $this->app_row['name'];
        $name_code          = $this->app_row['name_code'];
        $description        = $this->app_row['description'];
        $description_code   = $this->app_row['description_code'];
        $requirements       = $this->app_row['requirements'];
        $requirements_code  = $this->app_row['requirements_code'];
        if ($thread_id < 1)
        {
            SBC::devError('Thread ID is not set',$method);
        }
        if ($owner_user_id < 1)
        {
            SBC::devError('Application User ID is not set',$method);
        }
        if ($points < 1)
        {
            SBC::devError('Points is not set',$method);
        }
        if (empty($name))
        {
            SBC::devError('Name is not set',$method);
        }
        if (empty($description))
        {
            SBC::devError('Description is not set',$method);
        }
        if(empty($requirements))
        {
            SBC::devError('Requirements is not set',$method);
        }

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
            SBC::devError('Could not insert new challenge into database',$method);
        }
        $this->challenge_id = $challenge_id;

        // Create Challenge Tables
        $TableChallenge = new TableChallenge($challenge_id);
        $TableChallenge->checkTables($db);

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Update challenge as not deleted
        $sql = 'UPDATE challenges
            SET thread_id=?,
            isdeleted=0
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ii',$thread_id,$challenge_id);
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

    // Mail User with a Reply
    final private function mailUser(&$db)
    {
        $method = 'ChallengesPendingAdminSubmit->mailUser';

        // Initialize
        $mail_id    = $this->mail_id;
    }
}