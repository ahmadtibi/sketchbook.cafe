<?php
// @author          Kameloh
// @lastUpdated     2016-04-30

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\ForumAdmin\ForumAdmin as ForumAdmin;
use SketchbookCafe\Form\Form as Form;

class ForumThreadDeleteThreadForm
{
    public $Form;

    private $thread_id = 0;
    private $user_id = 0;
    private $hasinfo = 0;

    private $obj_array = [];

    // Construct
    public function __construct(&$obj_array)
    {
        $method = 'ForumThreadDeleteThreadForm->__construct()';

        // Set
        $this->obj_array    = &$obj_array;
    }

    // Set Thread ID
    final public function setThreadId($thread_id)
    {
        $method = 'ForumThreadDeleteThreadForm->setThreadId()';

        // Set
        $this->thread_id    = isset($thread_id) ? (int) $thread_id : 0;
        if ($this->thread_id < 1)
        {
            SBC::devError('Thread ID is not set',$method);
        }

        // Has Info
        $this->hasinfo = 1;
    }

    // Has Info
    final private function hasInfo()
    {
        $method = 'ForumThreadDeleteThreadForm->hasInfo()';

        if ($this->hasinfo != 1) 
        {
            SBC::devError('hasinfo is not set',$method);
        }
    }

    // Process
    final public function process()
    {
        $method = 'ForumThreadDeleteThreadForm->process()';

        // Has Info
        $this->hasInfo();

        // Initialize
        $db         = &$this->obj_array['db'];
        $User       = &$this->obj_array['User'];
        $thread_id  = $this->thread_id;

        // Open Connection
        $db->open();

        // User Required
        $User->required($db);
        $this->user_id = $User->getUserId();

        // Forum Admin
        $ForumAdmin = new ForumAdmin($this->obj_array);
        $ForumAdmin->setUserId($this->user_id);
        $ForumAdmin->getForumInfoByThreadId($thread_id);
        $ForumAdmin->process();
        $ForumAdmin->requireFlag('delete_thread');

        // Ckose Connection
        $db->close();

        // New Form
        $Form = new Form(array
        (
            'name'      => 'deletethreadform',
            'action'    => 'https://www.sketchbook.cafe/ajax/delete_thread_submit/',
            'method'    => 'POST',
        ));

        // Thread ID
        $Form->field['thread_id'] = $Form->hidden(array
        (
            'name'      => 'thread_id',
            'value'     => $thread_id,
        ));

        // Dropdown
        $list[' ']              = 0;
        $list['Delete Thread']  = 1;
        $input = array
        (
            'name'  => 'action',
        );
        $value = 0;
        $Form->field['action'] = $Form->dropdown($input,$list,$value);

        // Confirm Checkbox
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
            'name'      => 'Submit',
            'css'       => '',
        ));

        // Set 
        $this->Form = $Form;
    }
}