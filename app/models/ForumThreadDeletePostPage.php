<?php
// @author          Kameloh
// @lastUpdated     2016-05-20

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\ForumAdmin\ForumAdmin as ForumAdmin;
use SketchbookCafe\Form\Form as Form;

class ForumThreadDeletePostPage
{
    private $user_id = 0;
    private $Form;
    private $comment_id = 0;

    private $obj_array = [];

    // Construct
    public function __construct(&$obj_array)
    {
        $this->obj_array = &$obj_array;
    }

    // Set Comment ID
    final public function setCommentId($comment_id)
    {
        $method = 'ForumThreadDeletePostPage->setCommentId()';

        $this->comment_id = isset($comment_id) ? (int) $comment_id : 0;
        if ($this->comment_id < 1)
        {
            SBC::devError('Comment ID is not set',$method);
        }
    }

    // Process
    final public function process()
    {
        $method = 'ForumThreadDeletePostPage->process()';

        // Initialize
        $db         = &$this->obj_array['db'];
        $User       = &$this->obj_array['User'];
        $comment_id = $this->comment_id;
        if ($comment_id < 1)
        {
            SBC::devError('Comment ID is not set',$method);
        }

        // Open Connection
        $db->open();

        // User Required
        $User->required($db);
        $this->user_id = $User->getUserId();

        // Forum Admin
        $ForumAdmin = new ForumAdmin($this->obj_array);
        $ForumAdmin->setUserId($this->user_id);
        $ForumAdmin->getForumInfoByCommentId($comment_id);
        $ForumAdmin->process();
        $ForumAdmin->requireFlag('delete_post');

        // Close Connection
        $db->close();

        // Create Form
        $this->createForm();
    }

    // Create Form
    final private function createForm()
    {
        $method = 'ForumThreadDeletePostPage->createForm()';

        // New Form
        $Form = new Form(array
        (
            'name'      => 'deletepostform',
            'action'    => 'https://www.sketchbook.cafe/ajax/deletepost_submit/',
            'method'    => 'POST',
        ));

        // Comment ID
        $Form->field['comment_id'] = $Form->hidden(array
        (
            'name'      => 'comment_id',
            'value'     => $this->comment_id,
        ));

        // Dropdown
        $list[' ']              = 0;
        $list['Delete Post']    = 1;
        $input = array('name'=>'action');
        $value = 0;
        $Form->field['action'] = $Form->dropdown($input,$list,$value);

        // Confirm
        $Form->field['confirm'] = $Form->checkbox(array
        (
            'name'      => 'confirm',
            'value'     => 1,
            'checked'   => 0,
            'css'       => '',
        ));

        // Submit
        $Form->field['submit'] = $Form->submit(array
        (
            'name'  => 'Submit',
            'css'   => '',
        ));

        // Set
        $this->Form = $Form;
    }

    // Get Form
    final public function getForum()
    {
        return $this->Form;
    }
}