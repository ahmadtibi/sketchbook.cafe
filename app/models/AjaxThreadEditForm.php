<?php
echo 'models/AjaxThreadEditForm is no longer used';
/*
class AjaxThreadEditForm
{
    public $Form = '';
    private $user_id = 0;
    private $thread_id = 0;
    private $comment_id = 0;
    private $comment_row = [];

    // Construct
    public function __construct()
    {
    }

    // Set Thread ID
    final public function setThreadId($thread_id)
    {
        // Check
        $this->thread_id = isset($thread_id) ? (int) $thread_id : 0;
        if ($this->thread_id < 1)
        {
            error('Dev error: $thread_id is not set for AjaxThreadEditForm->setThreadId()');
        }
    }

    // Process
    final public function process(&$obj_array)
    {
        // Initialize Objects and Vars
        $db         = &$obj_array['db'];
        $User       = &$obj_array['User'];
        $thread_id  = $this->thread_id;

        // Classes and Functions
        sbc_class('Form');
        sbc_class('TextareaSettings');

        // Check
        if ($thread_id < 1)
        {
            error('Dev error: $thread_id is not set for AjaxThreadEditForm->process()');
        }

        // Open Connection
        $db->open();

        // User Required
        $User->required($db);
        $this->user_id = $User->getUserId();

        // Get Thread Information
        $this->getThreadInfo($db);

        // Get Comment Information
        $this->getCommentInfo($db);

        // Close Connection
        $db->close();

        // Set Vars
        $comment_row    = $this->comment_row;

        // New Form
        $Form   = new Form(array
        (
            'name'      => 'editforumthread',
            'action'    => 'https://www.sketchbook.cafe/',
            'method'    => 'POST',
        ));

        // Thread ID
        $Form->field['thread_id'] = $Form->hidden(array
        (
            'name'      => 'thread_id',
            'value'     => $thread_id,
        ));

        // Textarea Settings
        $TextareaSettings   = new TextareaSettings('forum_thread');
        $TextareaSettings->setValue($comment_row['message_code']);
        $message_settings   = $TextareaSettings->getSettings();

        // Textarea
        $Form->field['message'] = $Form->textarea($message_settings);

        // Set
        $this->Form = $Form;
    }

    // Get Thread Information
    final private function getThreadInfo(&$db)
    {
        // Initialize Vars
        $thread_id  = $this->thread_id;
        $user_id    = $this->user_id;
        $method     = 'AjaxThreadEditForm->getThreadInfo()';

        // Verify vars
        if ($thread_id < 1 || $user_id < 1)
        {
            error('Dev error: $thread_id:'.$thread_id.' and $user_id:'.$user_id.' for AjaxThreadEditForm->getThreadInfo()');
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Thread Information
        $sql = 'SELECT id, user_id, comment_id, is_locked, isdeleted
            FROM forum_threads
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$thread_id);
        if (!$stmt->execute())
        {
            statement_error('get thread information',$method);
        }
        $result = $stmt->get_result();
        $row    = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        $stmt->close();

        // Does the thread exist?
        $thread_id  = isset($row['id']) ? (int) $row['id'] : 0;
        if ($thread_id < 1)
        {
            error('Cannot find thread in database');
        }

        // Do they own the thread?
        if ($row['user_id'] != $user_id)
        {
            error('Sorry, you may only edit your own thread posts');
        }

        // Locked?
        if ($row['is_locked'] == 1)
        {
            error('Thread is locked and cannot be edited');
        }

        // Deleted?
        if ($row['isdeleted'] == 1)
        {
            error('Thread no longer exists');
        }

        // Set Comment ID
        $this->comment_id = $row['comment_id'];
        if ($this->comment_id < 1)
        {
            error('Dev error: $comment_id is not set for '.$method);
        }
    }

    // Get Comment Information
    final private function getCommentInfo(&$db)
    {
        // Initialize Vars
        $comment_id = $this->comment_id;
        $method     = 'AjaxThreadEditForm->getCommentInfo()';

        // Check
        if ($comment_id < 1)
        {
            error('Dev error: $comment_id:'.$comment_id.' for '.$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Comment Information
        $sql = 'SELECT id, message_code, isdeleted
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

        // Check
        $comment_id = isset($comment_row['id']) ? (int) $comment_row['id'] : 0;
        if ($comment_id < 1)
        {
            error('Dev error: cannot find comment for '.$method);
        }

        // Is Deleted
        if ($comment_row['isdeleted'] == 1)
        {
            error('Comment no longer exists');
        }

        // Set
        $this->comment_row = $comment_row;
    }
}
*/