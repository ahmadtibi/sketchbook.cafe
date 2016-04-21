<?php
// Ajax Controller

class Ajax extends Controller
{
    protected $obj_array = '';

    // Construct
    public function __construct(&$obj_array)
    {
        $this->obj_array = &$obj_array;
    }

    // Submit Edit
    public function edit_comment_submit($comment_id = 0)
    {
        // Comment ID
        $comment_id = isset($comment_id) ? (int) $comment_id : 0;
        if ($comment_id < 1)
        {
            error('Comment ID is not set');
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
            error('Dev error: $comment_id is not set');
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