<?php

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
        // Initialize Objects
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // Classes and Functions
        sbc_class('UserTimer');
        sbc_class('Message');
        sbc_class('TextareaSettings');
        sbc_class('BlockCheck');
        sbc_class('UpdateMailbox');
        sbc_function('get_username');
        sbc_function('rd');

        // Random Digit
        $this->rd = rd();

        // Username
        $username           = '';
        $username           = get_username($_POST['username']);
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
        // Classes + Functions
        sbc_class('TableMailbox');
        sbc_function('check_number');
        sbc_function('check_empty');

        // Initialize Vars
        $rd             = check_number($this->rd,'rd');
        $user_id        = check_number($this->user_id,'user_id');
        $r_user_id      = check_number($this->r_user_id,'r_user_id');
        $time           = time();
        $ip_address     = $_SERVER['REMOTE_ADDR'];

        // String Vars
        $title          = check_empty($this->title,'title');
        $title_code     = check_empty($this->title_code,'title_code');

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
        if (!$stmt->execute())
        {
            error('Could not execute statement (insert new mail thread) for ComposeNoteSubmit->createThread()');
        }
        $stmt->close();

        // Get Mail Thread ID
        $sql = 'SELECT id
            FROM mailbox_threads
            WHERE rd=?
            AND user_id=?
            AND r_user_id=?
            AND date_created=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('iiii',$rd,$user_id,$r_user_id,$time);
        if (!$stmt->execute())
        {
            error('Could not execute statement (get mail thread id) for ComposeNoteSubmit->createThread()');
        }
        $result = $stmt->get_result();
        $row    = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        $stmt->close();

        // Mail ID
        $mail_id    = isset($row['id']) ? (int) $row['id'] : 0;
        if ($mail_id < 1)
        {
            error('Dev error: could not get new mail id for ComposeNoteSubmit->createThread()');
        }
        $this->mail_id  = $mail_id;

        // Mark thread as not deleted
        $sql = 'UPDATE mailbox_threads
            SET isdeleted=0
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$mail_id);
        if (!$stmt->execute())
        {
            error('Could not execute statement (update isdeleted) for ComposeNoteSubmit->createThread()');
        }
        $stmt->close();

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
        // Initialize Vars
        $user_id    = $this->user_id;
        $r_user_id  = 0;
        $r_username = $this->r_username;
        if (empty($r_username))
        {
            error('Dev error: $r_username is not set for ComposeNoteSubmit->getOtherUser()');
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get User's Information
        $sql = 'SELECT id
            FROM users
            WHERE username=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('s',$r_username);
        if (!$stmt->execute())
        {
            error('Could not execute statement (get user information) for ComposeNoteSubmit->getOtherUser()');
        }
        $result = $stmt->get_result();
        $row    = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        $stmt->close();

        // Check if the user exists
        $r_user_id = isset($row['id']) ? (int) $row['id'] : 0;
        if ($r_user_id < 1)
        {
            error('Could not find user in database');
        }

        // Make sure we can't send messages to ourselves
        if ($r_user_id == $user_id)
        {
            error('Sorry, you cannot send mail to yourself');
        }

        // Set vars
        $this->r_user_id = $r_user_id;
    }

    // Update Users
    final private function updateUsers(&$db)
    {
        // Functions
        sbc_function('check_number');

        // Initialize Vars
        $user_id    = check_number($this->user_id,'$user_id');
        $r_user_id  = check_number($this->r_user_id,'$r_user_id');
        $mail_id    = check_number($this->mail_id,'$mail_id');
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
        if (!$stmt->execute())
        {
            error('Could not execute statement (insert into owners mail table) for ComposeNoteSubmit->updateUsers()');
        }
        $stmt->close();

        // Insert into other user's mailbox table
        $table_name = 'u'.$r_user_id.'m';
        $sql = 'INSERT INTO '.$table_name.'
            SET cid=?,
            lastupdate=?,
            isnew=1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ii',$mail_id,$time);
        if (!$stmt->execute())
        {
            error('Could not execute statement (insert into other users mail table) for ComposeNoteSubmit->updateUsers()');
        }
        $stmt->close();

        // Switch
        $db->sql_switch('sketchbookcafe');
    }
}
