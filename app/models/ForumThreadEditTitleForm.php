<?php
// @author          Kameloh
// @lastUpdated     2016-05-02

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\ForumAdmin\ForumAdmin as ForumAdmin;
use SketchbookCafe\Form\Form as Form;

class ForumThreadEditTitleForm
{
    public $Form;

    private $thread_id = 0;
    private $user_id = 0;
    private $hasinfo = 0;
    private $title_code = '';

    private $obj_array = [];

    // Construct
    public function __construct(&$obj_array)
    {
        $method = 'ForumThreadEditTitleForm->__construct()';

        $this->obj_array    = &$obj_array;
    }

    // Set Thread ID
    final public function setThreadId($thread_id)
    {
        $method = 'ForumThreadEditTitleForm->setThreadId()';

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
        $method = 'ForumThreadEditTitleForm->hasInfo()';

        if ($this->hasinfo != 1)
        {
            SBC::devError('hasinfo is not set',$method);
        }
    }

    // Process
    final public function process()
    {
        $method = 'ForumThreadEditTitleForm->process()';

        // Has info
        $this->hasInfo();

        // Initialize
        $db         = &$this->obj_array['db'];
        $User       = &$this->obj_array['User'];
        $thread_id  = $this->thread_id;

        // Open Connection
        $db->open();

        // User Required
        $User->required($db);
        $this->user_id  = $User->getUserId();

        // Forum Admin
        $ForumAdmin = new ForumAdmin($this->obj_array);
        $ForumAdmin->setUserId($this->user_id);
        $ForumAdmin->getForumInfoByThreadId($thread_id);
        $ForumAdmin->process();
        $ForumAdmin->requireFlag('edit_thread');

        // Get Thread Information
        $this->getThreadInfo($db);

        // Close Connection
        $db->close();

        // Edit Title Form
        $Form   = new Form(array
        (
            'name'      => 'edittitleform',
            'action'    => 'https://www.sketchbook.cafe/ajax/edit_threadtitle_submit/',
            'method'    => 'POST',
        ));

        // Thread ID
        $Form->field['thread_id'] = $Form->hidden(array
        (
            'name'      => 'thread_id',
            'value'     => $thread_id,
        ));

        // Title
        $Form->field['title'] = $Form->input(array
        (
            'name'          => 'name',
            'type'          => 'text',
            'max'           => 100,
            'value'         => $this->title_code,
            'placeholder'   => 'title',
            'css'           => 'input500 fpInputTitle',
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

    // Get Thread Info
    final private function getThreadInfo(&$db)
    {
        $method = 'ForumThreadEditTitleForm->getThreadInfo()';

        // Initialize
        $thread_id  = SBC::checkNumber($this->thread_id,'$this->thread_id');

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Thread Info
        $sql = 'SELECT id, title_code, isdeleted
            FROM forum_threads
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$thread_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Verify
        $thread_id  = isset($row['id']) ? (int) $row['id'] : 0;
        if ($thread_id < 1)
        {
            SBC::devError('Could not find thread in database',$method);
        }

        // Deleted?
        if ($row['isdeleted'] == 1)
        {
            SBC::userError('Thread no longer exists');
        }

        // Set
        $this->title_code   = $row['title_code'];
    }
}