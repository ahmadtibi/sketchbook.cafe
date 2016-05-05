<?php
// @author          Kameloh
// @lastUpdated     2016-04-29
// Ajax Controller

use SketchbookCafe\SBC\SBC as SBC;

class Ajax extends Controller
{
    protected $obj_array = '';

    // Construct
    public function __construct(&$obj_array)
    {
        $this->obj_array = &$obj_array;
    }

    // Thread: Edit Title
    public function edit_threadtitle($thread_id = 0)
    {
        $method = 'ajax->edit_threadtitle()';

        // Initialize
        $thread_id  = isset($thread_id) ? (int) $thread_id : 0;
        if ($thread_id < 1)
        {
            SBC::userError('Thread ID not specified');
        }

        // Model
        $ThreadObj  = $this->model('ForumThreadEditTitleForm',$this->obj_array);
        $ThreadObj->setThreadId($thread_id);
        $ThreadObj->process();
        $Form       = $ThreadObj->Form;

        // View
        $this->view('edit/edit_thread_title',
        [
            'Form'  => $Form,
        ]);
    }
    public function edit_threadtitle_submit()
    {
        $this->model('ForumThreadEditTitleSubmit',$this->obj_array);
    }

    // Thread: Delete Thread
    public function delete_thread($thread_id = 0)
    {
        $method = 'ajax->delete_thread()';

        // Initialize
        $thread_id = isset($thread_id) ? (int) $thread_id : 0;
        if ($thread_id < 1)
        {
            SBC::userError('Thread ID not specified');
        }

        // Model
        $ThreadObj  = $this->model('ForumThreadDeleteThreadForm',$this->obj_array);
        $ThreadObj->setThreadId($thread_id);
        $ThreadObj->process();
        $Form       = $ThreadObj->Form;

        // View
        $this->view('edit/delete_thread_form',
        [
            'Form'  => $Form,
        ]);
    }
    public function delete_thread_submit()
    {
        // Model
        $this->model('ForumThreadDeleteThreadSubmit',$this->obj_array);
    }

    // Thread: Sticky Form (no longer used)
    /*
    public function sticky_thread($comment_id = 0)
    {
        $method = 'ajax->sticky_thread()';

        // Initialize
        $comment_id = isset($comment_id) ? (int) $comment_id : 0;
        if ($comment_id < 1)
        {
            SBC::devError('Comment ID is not set',$method);
        }

        // Model
        $ThreadObject   = $this->model('ThreadStickyForm',$this->obj_array);
        $ThreadObject->setCommentId($comment_id);
        $ThreadObject->process();
        $Form           = $ThreadObject->Form;

        // View
        $this->view('edit/thread_sticky_form',
        [
            'Form'      => $Form,
        ]);
    }
    public function sticky_thread_submit()
    {
        // Model
        $this->model('ThreadStickySubmit',$this->obj_array);
    }
    */

    // Submit Edit
    public function edit_comment_submit($comment_id = 0)
    {
        $method = 'ajax->edit_comment_submit()';

        // Comment ID
        $comment_id = isset($comment_id) ? (int) $comment_id : 0;
        if ($comment_id < 1)
        {
            SBC::devError('Comment ID is not set',$method);
        }

        // Model
        $CommentObject  = $this->model('CommentEdit',$this->obj_array);
        $CommentObject->setCommentId($comment_id);
        $CommentObject->checkComment();

        // Message
        $message    = $CommentObject->message;

        // View
        $this->view('edit/edit_comment_view',
        [
            'message'   => $message,
        ]);
    }

    // Edit Comment
    public function edit_comment($comment_id = 0)
    {
        // Comment
        $comment_id = isset($comment_id) ? (int) $comment_id : 0;
        if ($comment_id < 1)
        {
            SBC::userError('$comment_id is not set');
        }

        // Model
        $CommentObject  = $this->model('CommentEdit',$this->obj_array);
        $CommentObject->setCommentId($comment_id);
        $CommentObject->getComment();
        $Form           = $CommentObject->Form;

        // View
        $this->view('edit/edit_comment_form',
        [
            'Form'  => $Form,
        ]);
    }

    // Main Page
    public function index()
    {
        // Model
        $this->model('AjaxMain',$this->obj_array);

        // View
        $this->view('ajax/index');
    }
}