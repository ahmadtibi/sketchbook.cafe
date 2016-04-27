<?php
// @author          Jonathan Maltezo (Kameloh)
// @lastUpdated     2016-04-27

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\BlockCheck\BlockCheck as BlockCheck;
use SketchbookCafe\UserTimer\UserTimer as UserTimer;
use SketchbookCafe\Message\Message as Message;
use SketchbookCafe\TextareaSettings\TextareaSettings as TextareaSettings;
use SketchbookCafe\UpdateMailbox\UpdateMailbox as UpdateMailbox;
use SketchbookCafe\SBCGetUsername\SBCGetUsername as SBCGetUsername;
use SketchbookCafe\TableMailbox\TableMailbox as TableMailbox;

class ComposeNoteSubmit
{
    private $r_username = '';
    private $r_user_id = 0;
    private $user_id = 0;

    private $rd = 0;
    private $title = '';
    private $title_code = '';
    private $mail_id = 0;

    // Construct
    public function __construct(&$obj_array)
    {
        $method = 'ComposeNoteSubmit->__construct()';

        // Initialize Objects
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // Random Digit
        $this->rd = SBC::rd();

        // Username
        $username           = '';
        $username           = SBCGetUsername::process($_POST['username']);
        $this->r_username   = $username;

        // Note Title
        $titleObject        = new Message(array
        (
            'name'          => 'title',
            'min'           => 3,
            'column_max'    => 250,
            'nl2br'         => 0,
            'basic'         => 0,
            'ajax'          => 0,
            'images'        => 0,
            'videos'        => 0,
        ));
        $titleObject->insert($_POST['title']);

        // Set Vars
        $this->title        = $titleObject->getMessage();
        $this->title_code   = $titleObject->getMessageCode();

        // Textarea Settings
        $TextareaSettings   = new TextareaSettings('composenote');
        $message_settings   = $TextareaSettings->getSettings();

        // Message
        $messageObject      = new Message($message_settings);
        $messageObject->insert($_POST['message']);

        // Open Connection
        $db->open();

        // User Required
        $User->required($db);
        $user_id = $User->getUserId();
        $this->user_id = $user_id;

        // User Timer
        $UserTimer = new UserTimer(array
        (
            'user_id'   => $user_id, 
        ));
        $UserTimer->setColumn('compose_note');
        $UserTimer->checkTimer($db);

        // Get User Information and Check
        $this->getOtherUser($db);

        // Set Vars
        $r_user_id = $this->r_user_id;

        // Check if they're blocking each other
        $BlockCheck = new BlockCheck(array
        (
            'user_id'       => $user_id,
            'r_user_id'     => $r_user_id,
        ));
        $BlockCheck->check($db);

        // Create New Mailbox Thread
        $this->createThread($db,$messageObject);

        // Update Users
        $this->updateUsers($db);

        // Update Mailbox Timers
        $mailbox_timer1 = new UpdateMailbox($user_id);
        $mailbox_timer2 = new UpdateMailbox($r_user_id);
        $mailbox_timer1->updateTimer($db);
        $mailbox_timer2->updateTimer($db);

        // User Timer
        $UserTimer->update($db);

        // Close Connection
        $db->close();

        // Header
        header('Location: https://www.sketchbook.cafe/mailbox/note/'.$this->mail_id.'/');
        exit;
    }

    // Create Thread
    final private function createThread(&$db,&$messageObject)
    {
        $method = 'ComposeNoteSubmit->createThread()';

        // Initialize Vars
        $rd             = SBC::checkNumber($this->rd,'rd');
        $user_id        = SBC::checkNumber($this->user_id,'user_id');
        $r_user_id      = SBC::checkNumber($this->r_user_id,'r_user_id');
        $time           = SBC::getTime();
        $ip_address     = SBC::getIpAddress();

        // String Vars
        $title          = SBC::checkEmpty($this->title,'title');
        $title_code     = SBC::checkEmpty($this->title_code,'title_code');

        // Create New Message
        $messageObject->setUserId($user_id);
        $messageObject->setType('new_mail_thread');
        $messageObject->createMessage($db);
        $comment_id = $messageObject->getCommentId();

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Insert new thread into database
        $sql = 'INSERT INTO mailbox_threads
            SET rd=?,
            user_id=?,
            r_user_id=?,
            ip_created=?,
            ip_updated=?,
            date_created=?,
            date_updated=?,
            title=?,
            title_code=?,
            comment_id=?,
            last_user_id=?,
            isdeleted=1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('iiissiissii',$rd,$user_id,$r_user_id,$ip_address,$ip_address,$time,$time,$title,$title_code,$comment_id,$user_id);
        SBC::statementExecute($stmt,$db,$sql,$method);

        // Get Mail Thread ID
        $sql = 'SELECT id
            FROM mailbox_threads
            WHERE rd=?
            AND user_id=?
            AND r_user_id=?
            AND date_created=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('iiii',$rd,$user_id,$r_user_id,$time);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Mail ID
        $mail_id    = isset($row['id']) ? (int) $row['id'] : 0;
        if ($mail_id < 1)
        {
            SBC::devError('could not get new mail id',$method);
        }
        $this->mail_id  = $mail_id;

        // Mark thread as not deleted
        $sql = 'UPDATE mailbox_threads
            SET isdeleted=0
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$mail_id);
        SBC::statementExecute($stmt,$db,$sql,$method);

        // Update Comment's Parent ID
        $messageObject->setParentId($comment_id);
        $messageObject->updateParentId($db);

        // Generate Mailbox Tables
        $TableMailbox = new TableMailbox($mail_id);
        $TableMailbox->checkTables($db);
    }

    // Get Other User Information
    final private function getOtherUser(&$db)
    {
        $method = 'ComposeNoteSubmit->getOtherUser()';

        // Initialize Vars
        $user_id    = $this->user_id;
        $r_user_id  = 0;
        $r_username = $this->r_username;
        if (empty($r_username))
        {
            SBC::devError('$r_username is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get User's Information
        $sql = 'SELECT id
            FROM users
            WHERE username=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('s',$r_username);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Check if the user exists
        $r_user_id = isset($row['id']) ? (int) $row['id'] : 0;
        if ($r_user_id < 1)
        {
            SBC::userError('Could not find user in database');
        }

        // Make sure we can't send messages to ourselves
        if ($r_user_id == $user_id)
        {
            SBC::userError('Sorry, you cannot send mail to yourself');
        }

        // Set vars
        $this->r_user_id = $r_user_id;
    }

    // Update Users
    final private function updateUsers(&$db)
    {
        $method = 'ComposeNoteSubmit->updateUsers()';

        // Initialize Vars
        $user_id    = SBC::checkNumber($this->user_id,'$user_id');
        $r_user_id  = SBC::checkNumber($this->r_user_id,'$r_user_id');
        $mail_id    = SBC::checkNumber($this->mail_id,'$mail_id');
        $time       = time();

        // Switch
        $db->sql_switch('sketchbookcafe_users');

        // Insert into owner's mailbox table
        $table_name = 'u'.$user_id.'m';
        $sql = 'INSERT INTO '.$table_name.'
            SET cid=?,
            lastupdate=?,
            replied=1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ii',$mail_id,$time);
        SBC::statementExecute($stmt,$db,$sql,$method);

        // Insert into other user's mailbox table
        $table_name = 'u'.$r_user_id.'m';
        $sql = 'INSERT INTO '.$table_name.'
            SET cid=?,
            lastupdate=?,
            isnew=1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ii',$mail_id,$time);
        SBC::statementExecute($stmt,$db,$sql,$method);

    }
}
