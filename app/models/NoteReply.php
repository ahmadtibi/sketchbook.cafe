<?php
// @author          Kameloh
// @lastUpdated     2016-05-11

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\UserTimer\UserTimer as UserTimer;
use SketchbookCafe\TextareaSettings\TextareaSettings as TextareaSettings;
use SketchbookCafe\Message\Message as Message;
use SketchbookCafe\BlockCheck\BlockCheck as BlockCheck;
use SketchbookCafe\UpdateMailbox\UpdateMailbox as UpdateMailbox;

class NoteReply
{
    private $mail_id = 0;
    private $user_id = 0;
    private $r_user_id = 0;
    private $ip_address = '';
    private $time = '';

    private $total = 0;
    private $comment_id = 0;

    // Construct
    public function __construct(&$obj_array)
    {
        $method = 'NoteReply->__construct()';

        // Initialize Objects
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // Mail ID
        $mail_id            = SBC::checkNumber($_POST['mail_id'],'$mail_id');
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

        // Mark thread as new
        $this->markThreadAsNew($db);

        // Update Mailbox Timers
        $mailbox_timer1 = new UpdateMailbox($user_id);
        $mailbox_timer2 = new UpdateMailbox($r_user_id);
        $mailbox_timer1->updateTimer($db);
        $mailbox_timer2->updateTimer($db);

        // User Timer
        $UserTimer->update($db);

        // Close Connection
        $db->close();

        // Calculate Page
        $ppage  = 10;
        $total  = $this->total;
        $pageno = SBC::currentPage($ppage,$total);

        // Header
        header('Location: https://www.sketchbook.cafe/mailbox/note/'.$mail_id.'/'.$pageno.'/#recent');
        exit;
    }

    // Get Mail Info
    final private function getMailInfo(&$db)
    {
        $method = 'NoteReply->getMailInfo()';

        // Initialize Vars
        $mail_id    = SBC::checkNumber($this->mail_id,'$mail_id');
        $user_id    = SBC::checkNumber($this->user_id,'$user_id');

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get mail information
        $sql = 'SELECT id, user_id, r_user_id, removed_user_id, removed_r_user_id, total_replies, isdeleted
            FROM mailbox_threads
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$mail_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Verify
        $mail_id    = isset($row['id']) ? (int) $row['id'] : 0;
        if ($mail_id < 1)
        {
            SBC::userError('Could not find mail thread in database');
        }

        // Can they reply to this mail?
        if ($row['user_id'] != $user_id)
        {
            if ($row['r_user_id'] != $user_id)
            {
                SBC::userError('Sorry, you do not have permission to reply to this note');
            }
        }

        // Who are they?
        $who        = '';
        $other      = '';
        $r_user_id  = 0;
        if ($row['user_id'] == $user_id)
        {
            $who        = 'user_id';
            $other      = 'r_user_id';
            $r_user_id  = $row['r_user_id'];
        }
        else if ($row['r_user_id'] == $user_id)
        {
            $who        = 'r_user_id';
            $other      = 'user_id';
            $r_user_id  = $row['user_id'];
        }
        else
        {
            SBC::devError('Something went wrong',$method);
        }

        // Check if they've already removed the mail from their mailbox
        if ($row['removed_'.$who] != 0)
        {
            SBC::userError('Sorry, this note no longer exists in your mailbox');
        }

        // Check if the other user removed this thread from their mailbox
        if ($row['removed_'.$other] != 0)
        {
            SBC::userError('Sorry, the other user has removed this from their mailbox');
        }

        // Check if it's deleted
        if ($row['isdeleted'] != 0)
        {
            SBC::userError('Note no longer exists');
        }

        // Set Vars
        $this->r_user_id    = $r_user_id;
        $this->total        = (int) $row['total_replies'];
    }

    // Create a Reply
    final private function createReply(&$db,&$messageObject)
    {
        $method = 'NoteReply->createReply()';

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
        $method = 'NoteReply->insertIntoTable()';

        // Initialize Vars
        $user_id    = $this->user_id;
        $mail_id    = $this->mail_id;
        $comment_id = $this->comment_id;
        $time       = $this->time;
        if ($comment_id < 1)
        {
            SBC::devError('$comment_id is not set',$method);
        }
        if ($mail_id < 1)
        {
            SBC::devError('$mail_id is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe_mailbox');

        // Insert into table
        $table_name = 'm'.$mail_id.'x';
        $sql = 'INSERT INTO '.$table_name.'
            SET cid=?';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$comment_id);
        SBC::statementExecute($stmt,$db,$sql,$method);

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
            SET date_updated=?,
            total_replies=?,
            last_user_id=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('iiii',$time,$total,$user_id,$mail_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }

    // Mark Thread as New
    final private function markThreadAsNew(&$db)
    {
        $method = 'NoteReply->markThread()';

        // Initialize Vars
        $r_user_id  = $this->r_user_id;
        $mail_id    = $this->mail_id;
        $time       = $this->time;

        // Check
        if ($r_user_id < 1 || $mail_id < 1)
        {
            SBC::devError('$r_user_id or $mail_id is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe_users');

        // Set Table
        $table_name = 'u'.$r_user_id.'m';

        // Update thread
        $sql = 'UPDATE '.$table_name.'
            SET lastupdate=?,
            isnew=1
            WHERE cid=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ii',$time,$mail_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }
}