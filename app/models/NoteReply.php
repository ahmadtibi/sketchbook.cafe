<?php

class NoteReply
{
    private $mail_id = 0;
    private $user_id = 0;
    private $r_user_id = 0;
    private $ip_address = '';
    private $time = '';

    private $comment_id = 0;

    // Construct
    public function __construct(&$obj_array)
    {
        // Initialize Objects
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // Classes and Functions
        sbc_class('UserTimer');
        sbc_class('TextareaSettings');
        sbc_class('Message');
        sbc_class('BlockCheck');
        sbc_class('UpdateMailbox');
        sbc_function('check_number');

        // Mail ID
        $mail_id            = check_number($_POST['mail_id'],'$mail_id');
        $this->mail_id      = $mail_id;

        // Textarea Settings
        $TextareaSettings   = new TextareaSettings('notereply');
        $message_settings   = $TextareaSettings->getSettings();

        // Message
        $messageObject      = new Message($message_settings);
        $messageObject->insert($_POST['notereply']);

        // Initialize Vars
        $this->ip_address   = $_SERVER['REMOTE_ADDR'];
        $this->time         = time();

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
        $UserTimer->setColumn('message');
        $UserTimer->checkTimer($db);

        // Get Mail Information
        $this->getMailInfo($db);

        // Set Vars
        $r_user_id  = $this->r_user_id;

        // Check if they're blocking each other
        $BlockCheck = new BlockCheck(array
        (
            'user_id'       => $user_id,
            'r_user_id'     => $r_user_id,
        ));
        $BlockCheck->check($db);

        // Create Reply
        $this->createReply($db,$messageObject);

        // Insert into mail table
        $this->insertIntoTable($db);

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
        header('Location: https://www.sketchbook.cafe/mailbox/note/'.$mail_id.'/');
        exit;
    }

    // Get Mail Info
    final private function getMailInfo(&$db)
    {
        // Functions
        sbc_function('check_number');

        // Initialize Vars
        $mail_id    = check_number($this->mail_id,'$mail_id');
        $user_id    = check_number($this->user_id,'$user_id');

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get mail information
        $sql = 'SELECT id, user_id, r_user_id, removed_user_id, removed_r_user_id, isdeleted
            FROM mailbox_threads
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$mail_id);
        if (!$stmt->execute())
        {
            error('Could not execute statement (get mail information) for NoteReply->getMailInfo()');
        }
        $result = $stmt->get_result();
        $row    = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        $stmt->close();

        // Verify
        $mail_id    = isset($row['id']) ? (int) $row['id'] : 0;
        if ($mail_id < 1)
        {
            error('Could not find mail thread in database');
        }

        // Can they reply to this mail?
        if ($row['user_id'] != $user_id)
        {
            if ($row['r_user_id'] != $user_id)
            {
                error('Sorry, you do not have permission to reply to this note');
            }
        }

        // Who are they?
        $who        = '';
        $r_user_id  = 0;
        if ($row['user_id'] == $user_id)
        {
            $who = 'user_id';
            $r_user_id = $row['r_user_id'];
        }
        else if ($row['r_user_id'] == $user_id)
        {
            $who = 'r_user_id';
            $r_user_id = $row['user_id'];
        }
        else
        {
            error('Dev error: Something went wrong in NoteReply->getMailInfo');
        }

        // Check if they've already removed the mail from their mailbox
        if ($row['removed_'.$who] != 0)
        {
            error('Sorry, this note no longer exists in your mailbox');
        }

        // Check if it's deleted
        if ($row['isdeleted'] != 0)
        {
            error('Note no longer exists');
        }

        // Set Vars
        $this->r_user_id    = $r_user_id;
    }

    // Create a Reply
    final private function createReply(&$db,&$messageObject)
    {
        // Classes and Functions
        sbc_function('check_number');
        sbc_function('check_empty');

        // Initialize Vars
        $mail_id    = $this->mail_id;
        $user_id    = $this->user_id;
        $time       = $this->time;
        $ip_address = $this->ip_address;

        // Create New Message
        $messageObject->setUserId($user_id);
        $messageObject->setType('note_reply');
        $messageObject->createMessage($db);
        $messageObject->setParentId($mail_id);
        $messageObject->updateParentId($db); // this looks awful - fix this later!
        $comment_id = $messageObject->getCommentId();

        // Set Comment Id
        $this->comment_id = $comment_id;
    }

    // Insert into the note's table
    final private function insertIntoTable(&$db)
    {
        // Initialize Vars
        $mail_id    = $this->mail_id;
        $comment_id = $this->comment_id;
        if ($comment_id < 1)
        {
            error('Dev error: $comment_id is not set for NoteReply->insertIntoTable()');
        }
        if ($mail_id < 1)
        {
            error('Dev error: $mail_id is not set for NoteReply->insertIntoTable()');
        }

        // Switch
        $db->sql_switch('sketchbookcafe_mailbox');

        // Insert into table
        $table_name = 'm'.$mail_id.'x';
        $sql = 'INSERT INTO '.$table_name.'
            SET cid=?';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$comment_id);
        if (!$stmt->execute())
        {
            error('Could not execute statement (insert into mail table) for NoteReply->insertIntoTable()');
        }
        $stmt->close();

        // Let's count since we're here
        $table_name = 'm'.$mail_id.'x';
        $sql = 'SELECT COUNT(*) 
            FROM '.$table_name;
        $result = $db->sql_query($sql);
        $row    = $db->sql_fetchrow($result);

        // Total
        $total = isset($row[0]) ? (int) $row[0] : 0;

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Update Mailbox Thread
        $sql = 'UPDATE mailbox_threads
            SET total_replies=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ii',$total,$mail_id);
        if (!$stmt->execute())
        {
            error('Could not execute statement (update total for mailbox thread) in NoteReply->insertIntoTable()');
        }
        $stmt->close();
    }
}