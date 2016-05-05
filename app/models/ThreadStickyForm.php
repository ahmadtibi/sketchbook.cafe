<?php
// @author          Kameloh
// @lastUpdated     2016-04-29

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\Form\Form as Form;
use SketchbookCafe\ForumAdmin\ForumAdmin as ForumAdmin;

class ThreadStickyForm
{
    public $form = '';
    private $is_sticky = 0;
    private $comment_id = 0;
    private $thread_id = 0;
    private $forum_id = 0;
    private $hasinfo = 0;
    private $obj_array = [];

    // Contsruct
    public function __construct(&$obj_array)
    {
        $this->obj_array = &$obj_array;
    }

    // Set Comment ID
    final public function setCommentId($comment_id)
    {
        $method = 'ThreadyStickyForm->setCommentId()';

        // Set
        $this->comment_id = isset($comment_id) ? (int) $comment_id : 0;
        if ($this->comment_id < 1)
        {
            SBC::devError('$comment_id is not set',$method);
        }

        // Has Info
        $this->hasinfo = 1;
    }

    // Has Info
    final private function hasInfo()
    {
        $method = 'ThreadyStickyForm->hasInfo()';
        if ($this->hasinfo != 1)
        {
            SBC::devError('$hasinfo is not set',$method);
        }
    }

    // Process
    final public function process()
    {
        $method = 'ThreadStickForm->process()';

        // Check
        $this->hasInfo();

        // Initialize
        $db     = &$this->obj_array['db'];
        $User   = &$this->obj_array['User'];

        // Open Connection
        $db->open();

        // User Required
        $User->required($db);

        // Forum Admin
        $ForumAdmin = new ForumAdmin($this->obj_array);
        $ForumAdmin->setUserId($User->getUserId());
        $ForumAdmin->getForumInfoByCommentId($this->comment_id);
        $ForumAdmin->process();
        $ForumAdmin->requireFlag('sticky_thread');

        $this->thread_id    = $ForumAdmin->getThreadId();

        // Get Sticky
        $this->getSticky($db);

        // Close Connection
        $db->close();

        // New Form
        $Form   = new Form(array
        (
            'name'      => 'stickythreadform',
            'action'    => 'https://www.sketchbook.cafe/ajax/sticky_thread_submit/',
            'method'    => 'POST',
        ));

        // Thread ID
        $Form->field['thread_id'] = $Form->hidden(array
        (
            'name'      => 'thread_id',
            'value'     => $this->thread_id,
        ));

        // Dropdown
        $list[' ']              = 0;
        if ($this->is_sticky == 0)
        {
            $list['Sticky Thread']  = 1;
        }
        else
        {
            $list['Un-Sticky Thread']  = 1;
        }
        $input  = array
        (
            'name'  => 'action',
        );
        $Form->field['action'] = $Form->dropdown($input,$list,0);

        // Submit
        $Form->field['submit'] = $Form->submit(array
        (
            'name'      => 'Submit',
            'css'       => '',
        ));

        // Set
        $this->Form = $Form;
    }

    // Get Sticky
    final private function getSticky(&$db)
    {
        $method = 'ThreadStickyForm->getSticky()';

        // Initialize
        $thread_id  = SBC::checkNumber($this->thread_id,'$thread_id');

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Sticky Info
        $sql = 'SELECT id, is_sticky
            FROM forum_threads
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$thread_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Double check
        $thread_id  = isset($row['id']) ? (int) $row['id'] : 0;
        if ($thread_id < 1)
        {
            SBC::devError('Cannot find thread...',$method);
        }

        // Set
        $this->is_sticky = $row['is_sticky'];
    }
}