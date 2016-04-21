<?php
// Comment Edit : forums, mailbox, user profiles
// Types:   1 - Note Post and Replies
//          2 - Forum Thread Post
//          3 - Forum Reply Post

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
        $this->comment_id = isset($comment_id) ? (int) $comment_id : 0;
        if ($this->comment_id < 1)
        {
            error('Dev error: $comment_id is not set for CommentEdit->setCommentId()');
        }
    }

    // Has Info
    final private function hasInfo()
    {
        if ($this->comment_id < 1 || $this->user_id < 1)
        {
            error('Dev error: $user_id('.$this->user_id.'), $comment_id('.$this->comment_id.') is not set for CommentEdit->hasInfo()');
        }
    }

    // Check Comment
    final public function checkComment()
    {
        // Initiailize Objects
        $db     = &$this->obj_array['db'];
        $User   = &$this->obj_array['User'];

        // Classes and Functions
        sbc_class('TextareaSettings');
        sbc_class('Message');

        // Initialize Vars
        $this->time         = time();
        $this->ip_address   = $_SERVER['REMOTE_ADDR'];

        // Open Connection
        $db->open();

        // User Required
        $User->required($db);
        $this->user_id = $User->getUserId();

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

        // Close Connection
        $db->close();
    }

    // Get Comment
    final public function getComment()
    {
        // Initiailize Vars and Objects
        $db     = &$this->obj_array['db'];
        $User   = &$this->obj_array['User'];

        // Check
        if ($this->comment_id < 1)
        {
            error('Dev error: $comment_id is not set for CommentEdit->getComment()');
        }

        // Classes and Functions
        sbc_class('Form');
        sbc_class('TextareaSettings');

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
        // Check Info?
        $this->hasInfo();

        // Initiailize Vars
        $comment_id = $this->comment_id;
        $user_id    = $this->user_id;
        $method     = 'CommentEdit->getCommentInfo()';

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Comment Information
        $sql = 'SELECT id, type, parent_id, user_id, message_code, isdeleted
            FROM sbc_comments
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$comment_id);
        if (!$stmt->execute())
        {
            statement_error('get comment info',$method);
        }
        $result         = $stmt->get_result();
        $comment_row    = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        $stmt->close();

        // Comment ID?
        $comment_id = isset($comment_row['id']) ? (int) $comment_row['id'] : 0;
        if ($comment_id < 1)
        {
            error('Could not find comment in database');
        }

        // Do they own the comment?
        if ($comment_row['user_id'] != $user_id)
        {
            error('Sorry, you may only edit comments that belong to you');
        }

        // Deleted?
        if ($comment_row['isdeleted'] == 1)
        {
            error('Comment no longer exists');
        }

        // Set
        $this->comment_row  = $comment_row;
        $this->message_code = $comment_row['message_code'];
        $this->parent_id    = $comment_row['parent_id'];

        // Type
        $type   = $comment_row['type'];
        if ($type < 1)
        {
            error('Dev error: $type is not set for CommentEdit->getCommentInfo()');
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

    // Mailbox Permissions
    final private function checkMailboxPermissions()
    {
        error('Sorry, you cannot edit mail posts');
    }

    // Forum Permissions
    final private function checkForumPermissions()
    {
        // Has Info
        $this->hasInfo();

        // Initialize Objects and Vars
        $db         = &$this->obj_array['db'];
        $comment_id = $this->comment_id;
        $user_id    = $this->user_id;
        $thread_id  = $this->parent_id;
        $method     = 'CommentEdit->checkForumPermissions()';

        // Check
        if ($thread_id < 1)
        {
            error('Dev error: $thread_id is not set for '.$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Thread Information
        $sql = 'SELECT id, forum_id, is_locked, isdeleted
            FROM forum_threads
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$thread_id);
        if (!$stmt->execute())
        {
            statement_error('get thread info',$method);
        }
        $result     = $stmt->get_result();
        $thread_row = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        $stmt->close();

        // Thread ID
        $thread_id  = isset($thread_row['id']) ? (int) $thread_row['id'] : 0;
        if ($thread_id < 1)
        {
            error('Dev error: could not find thread for '.$method);
        }

        // Thread Locked?
        if ($thread_row['is_locked'] == 1)
        {
            error('Thread is locked');
        }

        // Deleted?
        if ($thread_row['isdeleted'] == 1)
        {
            error('Thread no longer exists');
        }

        // Forum ID
        $forum_id   = $thread_row['forum_id'];
        if ($forum_id < 1)
        {
            error('Thread does not belong to a forum');
        }

        // Check Forum Info
        $sql = 'SELECT id, isdeleted
            FROM forums
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$forum_id);
        if (!$stmt->execute())
        {
            statement_error('get forum info',$method);
        }
        $result     = $stmt->get_result();
        $forum_row  = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        $stmt->close();

        // Check
        $forum_id   = isset($forum_row['id']) ? (int) $forum_row['id'] : 0;
        if ($forum_id < 1)
        {
            error('Thread does not have a forum set');
        }

        // Deleted?
        if ($forum_row['isdeleted'] == 1)
        {
            error('Forum no longer exists');
        }
    }

    // Update Comment
    final private function updateComment(&$db,&$messageObject)
    {
        // Has Info
        $this->hasInfo();

        // Initialize Vars
        $time           = $this->time;
        $comment_id     = $this->comment_id;
        $ip_address     = $this->ip_address;
        $message        = $messageObject->getMessage();
        $message_code   = $messageObject->getMessageCode();
        $method         = 'CommentEdit->updateComment()';

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
        if (!$stmt->execute())
        {
            statement_error('update comment',$method);
        }
        $stmt->close();

        // Set Message
        $this->message = $message;
    }
}