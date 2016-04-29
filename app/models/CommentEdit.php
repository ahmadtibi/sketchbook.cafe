<?php
// @author          Jonathan Maltezo (Kameloh)
// @lastUpdated     2016-04-27
// Comment Edit : forums, mailbox, user profiles
// Types:   1 - Note Post and Replies
//          2 - Forum Thread Post
//          3 - Forum Reply Post

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\TextareaSettings\TextareaSettings as TextareaSettings;
use SketchbookCafe\Message\Message as Message;
use SketchbookCafe\UserTimer\UserTimer as UserTimer;
use SketchbookCafe\Form\Form as Form;

class CommentEdit
{
    // Comment Editing
    private $time = 0;
    private $ip_address = '';
    public $message = '';

    private $comment_id = 0;
    private $user_id = 0;
    private $parent_id = 0;
    private $comment_row = [];
    private $message_code = '';
    private $textarea_settings = '';

    private $obj_array = [];

    // Construct
    public function __construct(&$obj_array)
    {
        // Set Objects
        $this->obj_array = &$obj_array;
    }

    // Set Comment ID
    final public function setCommentId($comment_id)
    {
        $method = 'CommentEdit->setCommentId()';

        $this->comment_id = isset($comment_id) ? (int) $comment_id : 0;
        if ($this->comment_id < 1)
        {
            SBC::devError('$comment_id is not set',$method);
        }
    }

    // Has Info
    final private function hasInfo()
    {
        $method = 'CommentEdit->hasInfo()';

        if ($this->comment_id < 1 || $this->user_id < 1)
        {
            SBC::devError('$user_id('.$this->user_id.'), $comment_id('.$this->comment_id.') is not set',$method);
        }
    }

    // Check Comment
    final public function checkComment()
    {
        $method = 'CommentEdit->checkComment()';

        // Initiailize Objects
        $db     = &$this->obj_array['db'];
        $User   = &$this->obj_array['User'];

        // Initialize Vars
        $this->time         = SBC::getTime();
        $this->ip_address   = SBC::getIpAddress();

        // Open Connection
        $db->open();

        // User Required
        $User->required($db);
        $this->user_id = $User->getUserId();

        // User Timer
        $UserTimer = new UserTimer(array
        (
            'user_id'   => $this->user_id,
        ));
        $UserTimer->setColumn('edit_comment');
        $UserTimer->checkTimer($db);

        // Get Comment Information
        $this->getCommentInfo($db);

        // Textarea Settings
        $TextareaSettings   = new TextareaSettings('forum_reply');
        $message_settings   = $TextareaSettings->getSettings();

        // Message
        $messageObject      = new Message($message_settings);
        $messageObject->insert($_POST['message']);

        // Update Message
        $this->updateComment($db,$messageObject);

        // Create Comment Log
        $this->logOldComment($db,$messageObject);

        // Update User Timer
        $UserTimer->update($db);

        // Close Connection
        $db->close();
    }

    // Get Comment
    final public function getComment()
    {
        $method = 'CommentEdit->getComment()';

        // Initiailize Vars and Objects
        $db     = &$this->obj_array['db'];
        $User   = &$this->obj_array['User'];

        // Check
        if ($this->comment_id < 1)
        {
            SBC::devError('$comment_id is not set',$method);
        }

        // Open Connection
        $db->open();

        // User Required
        $User->required($db);
        $this->user_id = $User->getUserId();

        // Get Comment Information
        $this->getCommentInfo($db);

        // Close Connection
        $db->close();

        // New Form
        $Form   = new Form(array
        (
            'name'      => 'commenteditform'.$this->comment_id,
            'action'    => 'https://www.sketchbook.cafe/test.php',
            'method'    => 'POST',
        ));

        // Set Javascript
        $Form->setJavascript('sbc_edit_submit_form('.$this->comment_id.'); return false;');

        // Comment ID
        $Form->field['comment_id']  = $Form->hidden(array
        (
            'name'      => 'comment_id',
            'value'     => $this->comment_id,
        ));

        // Textarea Settings
        $TextareaSettings   = new TextareaSettings($this->textarea_settings);
        $TextareaSettings->setValue($this->message_code);
        $message_settings   = $TextareaSettings->getSettings();

        // Message
        $Form->field['message'] = $Form->textarea($message_settings);

        // Set
        $this->Form = $Form;
    }

    // Get Comment Information
    final private function getCommentInfo(&$db)
    {
        $method = 'CommentEdit->getCommentInfo()';

        // Check Info?
        $this->hasInfo();

        // Initiailize Vars
        $comment_id = $this->comment_id;
        $user_id    = $this->user_id;

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Comment Information
        $sql = 'SELECT id, type, parent_id, user_id, message_code, isdeleted
            FROM sbc_comments
            WHERE id=?
            LIMIT 1';
        $stmt           = $db->prepare($sql);
        $stmt->bind_param('i',$comment_id);
        $comment_row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Comment ID?
        $comment_id = isset($comment_row['id']) ? (int) $comment_row['id'] : 0;
        if ($comment_id < 1)
        {
            SBC::userError('Could not find comment in database');
        }

        // Edit Permissions?
        if ($comment_row['user_id'] != $user_id)
        {
            // Check Edit Permissions
            $this->checkEditPermission($comment_row['type'],$comment_row['parent_id']);
        }

        // Deleted?
        if ($comment_row['isdeleted'] == 1)
        {
            SBC::userError('Comment no longer exists');
        }

        // Set
        $this->comment_row  = $comment_row;
        $this->message_code = $comment_row['message_code'];
        $this->parent_id    = $comment_row['parent_id'];

        // Type
        $type   = $comment_row['type'];
        if ($type < 1)
        {
            SBC::devError('$type is not set',$method);
        }

        // Mailbox
        if ($type == 1)
        {
            $this->textarea_settings = 'note_reply';
            $this->checkMailboxPermissions();
        }
        // Forum Threads and Posts
        else if ($type == 2 || $type == 3)
        {
            $this->checkForumPermissions();
            if ($type == 2)
            {
                $this->textarea_settings = 'forum_thread';
            }
            else
            {
                $this->textarea_settings = 'forum_reply';
            }
        }
    }

    // Check Edit Permissions (forum threads, forum posts)
    final private function checkEditPermission($type,$parent_id)
    {
        $method = 'CommentEdit->checkEditPermissions()';

        // Initialize
        $User       = &$this->obj_array['User'];
        $db         = &$this->obj_array['db'];
        $allow_edit = 0;

        // Type Check
        if ($type == 2 || $type == 3)
        {
            // Set
            $thread_id  = $parent_id;

            // Admin Check?
            if ($User->isAdmin() && $thread_id > 0)
            {
                // Switch
                $db->sql_switch('sketchbookcafe');

                // Get Thread Info
                $sql = 'SELECT id, forum_id
                    FROM forum_threads
                    WHERE id=?
                    LIMIT 1';
                $stmt   = $db->prepare($sql);
                $stmt->bind_param('i',$thread_id);
                $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

                // Thread?
                $thread_id  = isset($row['id']) ? (int) $row['id'] : 0;
                if ($thread_id > 0)
                {
                    // Set Forum ID
                    $forum_id   = $row['forum_id'];

                    // Check if Forum Admin
                    $User->getForumAdminFlags($db,$forum_id);

                    // Thread
                    if ($type == 2 && $User->hasForumAdminFlag('edit_thread'))
                    {
                        $allow_edit = 1;
                    }
                    else if ($type == 3 && $User->hasForumAdminFlag('edit_post'))
                    {
                        $allow_edit = 1;
                    }
                }
            }
        }

        // Allow Edit?
        if ($allow_edit != 1)
        {
            SBC::userError('Sorry, you may only edit comments that belong to you');
        }
    }

    // Mailbox Permissions
    final private function checkMailboxPermissions()
    {
        SBC::userError('Sorry, you cannot edit mail posts');
    }

    // Forum Permissions
    final private function checkForumPermissions()
    {
        $method = 'CommentEdit->checkForumPermissions()';

        // Has Info
        $this->hasInfo();

        // Initialize Objects and Vars
        $db         = &$this->obj_array['db'];
        $comment_id = $this->comment_id;
        $user_id    = $this->user_id;
        $thread_id  = $this->parent_id;

        // Check
        if ($thread_id < 1)
        {
            SBC::devError('$thread_id is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Thread Information
        $sql = 'SELECT id, forum_id, is_locked, isdeleted
            FROM forum_threads
            WHERE id=?
            LIMIT 1';
        $stmt       = $db->prepare($sql);
        $stmt->bind_param('i',$thread_id);
        $thread_row = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Thread ID
        $thread_id  = isset($thread_row['id']) ? (int) $thread_row['id'] : 0;
        if ($thread_id < 1)
        {
            SBC::devError('could not find thread',$method);
        }

        // Thread Locked?
        if ($thread_row['is_locked'] == 1)
        {
            SBC::userError('Thread is locked');
        }

        // Deleted?
        if ($thread_row['isdeleted'] == 1)
        {
            SBC::userError('Thread no longer exists');
        }

        // Forum ID
        $forum_id   = $thread_row['forum_id'];
        if ($forum_id < 1)
        {
            SBC::userError('Thread does not belong to a forum');
        }

        // Check Forum Info
        $sql = 'SELECT id, isdeleted
            FROM forums
            WHERE id=?
            LIMIT 1';
        $stmt       = $db->prepare($sql);
        $stmt->bind_param('i',$forum_id);
        $forum_row  = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Check
        $forum_id   = isset($forum_row['id']) ? (int) $forum_row['id'] : 0;
        if ($forum_id < 1)
        {
            SBC::userError('Thread does not have a forum set');
        }

        // Deleted?
        if ($forum_row['isdeleted'] == 1)
        {
            SBC::userError('Forum no longer exists');
        }
    }

    // Update Comment
    final private function updateComment(&$db,&$messageObject)
    {
        $method = 'CommentEdit->updateComment()';

        // Has Info
        $this->hasInfo();

        // Initialize Vars
        $time           = $this->time;
        $comment_id     = $this->comment_id;
        $ip_address     = $this->ip_address;
        $message        = $messageObject->getMessage();
        $message_code   = $messageObject->getMessageCode();

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Update Comment
        $sql = 'UPDATE sbc_comments
            SET date_updated=?,
            ip_updated=?,
            message=?,
            message_code=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('isssi',$time,$ip_address,$message,$message_code,$comment_id);
        SBC::statementExecute($stmt,$db,$sql,$method);

        // Set Message
        $this->message = $message;
    }

    // Log Old Comment
    final private function logOldComment(&$db,&$messageObject)
    {
        $method = 'CommentEdit->logOldComment()';

        // Initialize Vars
        $user_id            = $this->user_id;
        $ip_address         = $this->ip_address;
        $time               = $this->time;
        $comment_id         = $this->comment_id;
        $old_message        = $messageObject->getMessage();
        $old_message_code   = $messageObject->getMessageCode();

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Insert into log
        $sql = 'INSERT INTO log_comment_edit
            SET user_id=?,
            comment_id=?,
            ip_created=?,
            date_created=?,
            old_message=?,
            old_message_code=?';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('iisiss',$user_id,$comment_id,$ip_address,$time,$old_message,$old_message_code);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }
}