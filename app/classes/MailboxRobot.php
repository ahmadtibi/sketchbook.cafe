<?php
// @author          Kameloh
// @lastUpdated     2016-05-19
namespace SketchbookCafe\MailboxRobot;

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\TextareaSettings\TextareaSettings as TextareaSettings;
use SketchbookCafe\Message\Message as Message;
use SketchbookCafe\TableMailbox\TableMailbox as TableMailbox;
use SketchbookCafe\UserOrganizer\UserOrganizer as UserOrganizer;

class MailboxRobot
{
    private $ip_address = '';
    private $rd = 0;
    private $time = 0;
    private $robot_user_id = 3; // default robot ID
    private $r_user_id = 0; // recipient

    private $title = '';
    private $title_code = '';
    private $messageObj;

    private $comment_id = 0;
    private $mail_id = 0;
    private $reply_id = 0;

    private $db;

    // Construct
    public function __construct(&$db)
    {
        $this->db           = &$db;
        $this->rd           = SBC::rd();
        $this->time         = SBC::getTime();
        $this->ip_address   = SBC::getIpAddress();
    }

    // Set User ID
    final public function setUserId($r_user_id)
    {
        $method = 'MailboxRobot->setUserId()';

        $this->r_user_id = (int) $r_user_id;
        if ($this->r_user_id < 1)
        {
            SBC::devError('User ID is not set',$method);
        }
    }

    // Set Reply ID
    final public function setReplyId($mail_id)
    {
        $method = 'MailboxRobot->setReplyId()';

        $this->reply_id = $mail_id;
        if ($this->reply_id < 1)
        {
            SBC::devError('Reply ID is not set',$method);
        }
    }

    // Set Title
    final public function setTitle($title)
    {
        $method = 'MailboxRobot->setTitle()';

        $titleObj   = new Message(array
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
        $titleObj->insert($title);

        // Set vars
        $this->title        = $titleObj->getMessage();
        $this->title_code   = $titleObj->getMessageCode();
    }

    // Set Message
    final public function setMessage($message)
    {
        $method = 'MailboxRobot->setMessage()';

        $tsObj      = new TextareaSettings('composenote');
        $messageObj = new Message($tsObj->getSettings());
        $messageObj->insert($message);
        $this->messageObj = &$messageObj;
    }

    // Verify User
    final private function verifyUser($user_id)
    {
        $method = 'MailboxRobot->verifyUser('.$user_id.')';

        $db     = &$this->db;
        if ($user_id < 1)
        {
            SBC::devError('User ID is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get user ID
        $sql = 'SELECT id
            FROM users
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$user_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // ID?
        $user_id    = isset($row['id']) ? (int) $row['id'] : 0;
        if ($user_id < 1)
        {
            SBC::devError('Could not find user in database',$method);
        }
    }

    // Create Mail
    final public function createMail()
    {
        $method = 'MailboxRobot->createMail()';

        // Initialize
        $db             = &$this->db;
        $rd             = $this->rd;
        $time           = $this->time;
        $ip_address     = $this->ip_address;
        $r_user_id      = $this->r_user_id; // recipient
        $user_id        = $this->robot_user_id; // robot or thread owner
        $title          = $this->title;
        $title_code     = $this->title_code;
        $messageObj     = &$this->messageObj;

        // Double check users
        $this->verifyUser($user_id);
        $this->verifyUser($r_user_id);
        $table_u    = 'u'.$user_id.'m';
        $table_r    = 'u'.$r_user_id.'m';

        // Check Values
        if (empty($title) || empty($title_code))
        {
            SBC::devError('Title is empty',$method);
        }
        if (empty($messageObj))
        {
            SBC::devError('Message object is not set',$method);
        }

        // Insert new comment + Get Comment ID
        $messageObj->setUserId($user_id);
        $messageObj->setType('new_mail_thread');
        $messageObj->createMessage($db);
        $comment_id = $messageObj->getCommentId();
        if ($comment_id < 1)
        {
            SBC::devError('Could not insert new comment into database',$method);
        }
        $this->comment_id = $comment_id;

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Insert into mailbox threads
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

        // Get Mail ID
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

        // Verify Mail ID
        $mail_id    = isset($row['id']) ? (int) $row['id'] : 0;
        if ($mail_id < 1)
        {
            SBC::devError('could not get new mail id',$method);
        }
        $this->mail_id  = $mail_id;

        // Mark mail as not deleted
        $sql = 'UPDATE mailbox_threads
            SET isdeleted=0
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$mail_id);
        SBC::statementExecute($stmt,$db,$sql,$method);

        // Update comment's parent ID
        $messageObj->setParentId($comment_id);
        $messageObj->updateParentId($db);

        // Generate Mailbox Tables
        $TableMailbox = new TableMailbox($mail_id);
        $TableMailbox->checkTables($db);

        // Switch
        $db->sql_switch('sketchbookcafe_users');

        // Insert into owner's mailbox table (or robot)
        $sql = 'INSERT INTO '.$table_u.'
            SET cid=?,
            lastupdate=?,
            replied=1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ii',$mail_id,$time);
        SBC::statementExecute($stmt,$db,$sql,$method);

        // No duplicates - fixed the "new mail" errors
        if ($user_id != $r_user_id)
        {
            // Insert into user's mailbox table
            $sql = 'INSERT INTO '.$table_r.'
                SET cid=?,
                lastupdate=?,
                isnew=1';
            $stmt = $db->prepare($sql);
            $stmt->bind_param('ii',$mail_id,$time);
            SBC::statementExecute($stmt,$db,$sql,$method);
        }

        // User Organizer
        $UserOrganizer = new UserOrganizer($db);
        $UserOrganizer->updateMailTimer($user_id);
        $UserOrganizer->updateMailTimer($r_user_id);
    }

    // Get Mail ID
    final public function getMailId()
    {
        return $this->mail_id;
    }

    // Verify Mail
    final private function verifyMail($mail_id)
    {
        $method = 'MailboxRobot->verifyMail()';

        if ($mail_id < 1)
        {
            SBC::devError('Mail ID is not set',$method);
        }

        $db = &$this->db;

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get mail thread
        $sql = 'SELECT id
            FROM mailbox_threads
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$mail_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Verify
        $mail_id = isset($row['id']) ? (int) $row['id'] : 0;
        if ($mail_id < 1)
        {
            SBC::devError('Could not find mail thread in database',$method);
        }
    }

    // Reply
    final public function createReply()
    {
        $method = 'MailboxRobot->createMail()';

        // Initialize
        $db             = &$this->db;
        $rd             = $this->rd;
        $time           = $this->time;
        $ip_address     = $this->ip_address;
        $r_user_id      = $this->r_user_id; // recipient
        $user_id        = $this->robot_user_id; // robot or thread owner
        $messageObj     = &$this->messageObj;
        $mail_id        = $this->reply_id;
        $table_name     = 'm'.$mail_id.'x';
        $table_user     = 'u'.$r_user_id.'m';
        if ($mail_id < 1)
        {
            SBC::devError('Reply ID is not set',$method);
        }
        if (empty($messageObj))
        {
            SBC::devError('Message is not set',$method);
        }

        // Verify User
        $this->verifyUser($user_id);
        $this->verifyUser($r_user_id);

        // Verify Mail
        $this->verifyMail($mail_id);

        // Create New Message
        $messageObj->setUserId($user_id);
        $messageObj->setType('note_reply');
        $messageObj->createMessage($db);
        $messageObj->setParentId($mail_id);
        $messageObj->updateParentId($db);
        $comment_id = $messageObj->getCommentId();
        $this->comment_id = $comment_id;

        // Switch
        $db->sql_switch('sketchbookcafe_mailbox');

        // Insert into thread's table
        $sql = 'INSERT INTO '.$table_name.'
            SET cid=?';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$comment_id);
        SBC::statementExecute($stmt,$db,$sql,$method);

        // Let's count since we're here
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

        // Switch
        $db->sql_switch('sketchbookcafe_users');

        // Update user's thread as new
        $sql = 'UPDATE '.$table_user.'
            SET lastupdate=?,
            isnew=1
            WHERE cid=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ii',$time,$mail_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }
}