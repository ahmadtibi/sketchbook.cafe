<?php
// @author          Jonathan Maltezo (Kameloh)
// @lastUpdated     2016-04-27

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\Form\Form as Form;

class AdminEditForumAdminPage
{
    public $Form = '';
    public $forum_row = [];
    private $admin_id = 0;
    private $admin_user_id = 0;
    private $admin_forum_id = 0;
    public $admin_flags = [];

    private $user_id = 0;
    private $ip_address = '';
    private $time = 0;

    // Construct
    public function __construct($admin_id)
    {
        $method = 'AdminEditForumAdminPage->__construct()';

        // Set ID
        $this->admin_id = isset($admin_id) ? (int) $admin_id : 0;
        if ($this->admin_id < 1)
        {
            SBC::devError('$admin_id is not set',$method);
        }
    }

    // Process
    final public function process(&$obj_array)
    {
        $method = 'AdminEditForumAdminPage->process()';

        // Initialize Objects
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];
        $Member = &$obj_array['Member'];

        // Open Connection
        $db->open();

        // Admin Required + Process Data
        $User->setFrontpage();
        $User->admin($db);
        $User->requireAdminFlag('manage_forum_admins');

        // Get Forum Admin Info
        $this->getAdminInfo($db,$Member);

        // Get Forum Info
        $this->getForumInfo($db);

        // Process All User Data Last!
        $ProcessAllData = new ProcessAllData();

        // Close Connection
        $db->close();

        // New Form
        $Form   = new Form(array
        (
            'name'      => 'editforumadminform',
            'action'    => 'https://www.sketchbook.cafe/admin/edit_forum_admin_submit/',
            'method'    => 'POST',
        ));

        // Submit
        $Form->field['submit'] = $Form->submit(array
        (
            'name'  => 'submit',
            'css'   => '',
        ));

        // Admin id
        $Form->field['admin_id'] = $Form->hidden(array
        (
            'name'  => 'admin_id',
            'value' => $this->admin_id,
        ));

        // Lock Thread
        $Form->field['lock_thread'] = $Form->checkbox(array
        (
            'name'      => 'lock_thread',
            'value'     => 1,
            'checked'   => $this->admin_flags['lock_thread'],
            'css'       => '',
        ));

        // Lock Post
        $Form->field['lock_post'] = $Form->checkbox(array
        (
            'name'      => 'lock_post',
            'value'     => 1,
            'checked'   => $this->admin_flags['lock_post'],
            'css'       => '',
        ));

        // Bump Thread
        $Form->field['bump_thread'] = $Form->checkbox(array
        (
            'name'      => 'bump_thread',
            'value'     => 1,
            'checked'   => $this->admin_flags['bump_thread'],
            'css'       => '',
        ));

        // Move Thread
        $Form->field['move_thread'] = $Form->checkbox(array
        (
            'name'      => 'move_thread',
            'value'     => 1,
            'checked'   => $this->admin_flags['move_thread'],
            'css'       => '',
        ));

        // Sticky Thread
        $Form->field['sticky_thread'] = $Form->checkbox(array
        (
            'name'      => 'sticky_thread',
            'value'     => 1,
            'checked'   => $this->admin_flags['sticky_thread'],
            'css'       => '',
        ));

        // Edit Thread
        $Form->field['edit_thread'] = $Form->checkbox(array
        (
            'name'      => 'edit_thread',
            'value'     => 1,
            'checked'   => $this->admin_flags['edit_thread'],
            'css'       => '',
        ));

        // Edit Post
        $Form->field['edit_post'] = $Form->checkbox(array
        (
            'name'      => 'edit_post',
            'value'     => 1,
            'checked'   => $this->admin_flags['edit_post'],
            'css'       => '',
        ));

        // Set
        $this->Form = $Form;
    }

    // Get Admin Info
    final private function getAdminInfo(&$db,&$Member)
    {
        $method = 'AdminEditForumAdminPage->getAdminInfo()';

        // Initialize Vars
        $admin_id   = $this->admin_id;

        // Check
        if ($admin_id < 1)
        {
            SBC::devError('$admin_id is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Admin Info
        $sql = 'SELECT id, user_id, forum_id, lock_thread, lock_post, bump_thread, move_thread,
            sticky_thread, edit_thread, edit_post
            FROM forum_admins
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$admin_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Check
        $admin_id   = isset($row['id']) ? (int) $row['id'] : 0;
        if ($admin_id < 1)
        {
            SBC::devError('Could not find forum admin',$method);
        }

        // Set Vars
        $this->admin_flags      = $row;
        $this->admin_user_id    = $row['user_id'];
        $this->admin_forum_id   = $row['forum_id'];

        // Add Member
        $Member->idAddOne($this->admin_user_id);
    }

    // Get Forum Info
    final private function getForumInfo(&$db)
    {
        $method = 'AdminEditForumAdminPage->getForumInfo()';

        // Initialize Vars
        $forum_id   = $this->admin_forum_id;

        // Check
        if ($forum_id < 1)
        {
            SBC::devError('$forum_id is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Forum Info
        $sql = 'SELECT id, name
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
            SBC::devError('Admin does not have a forum ID set',$method);
        }

        // Set
        $this->forum_row = $forum_row;
    }
}