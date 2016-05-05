<?php
// @author          Kameloh
// @lastUpdated     2016-04-27

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\Form\Form as Form;

class AdminForumAdminsPage
{
    public $Form = '';
    public $forums_result = '';
    public $forums_rownum = 0;

    public $f_admin_result = '';
    public $f_admin_rownum = 0;

    // Construct
    public function __construct(&$obj_array)
    {
        $method = 'AdminForumAdminsPage->__construct()';

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

        // Get Forums
        $this->getForums($db);

        // Get Administrators
        $this->getForumAdmins($db,$Member);

        // Process All User Data Last!
        $ProcessAllData = new ProcessAllData();

        // Close Connection
        $db->close();

        // Create a Forum List
        $list_value = 0;
        $list_input = array
        (
            'name'  => 'forum_id',
        );
        $list = [];
        while ($trow = mysqli_fetch_assoc($this->forums_result))
        {
            $temp_name          = $trow['name'];
            $list[$temp_name]   = $trow['id'];
        }
        mysqli_data_seek($this->forums_result,0);


        // New Form
        $Form   = new Form(array
        (
            'name'      => 'newforumadminform',
            'action'    => 'https://www.sketchbook.cafe/admin/manage_forum_admins_submit/',
            'method'    => 'POST',
        ));

        // Forums
        $Form->field['forum_id'] = $Form->dropdown($list_input,$list,$list_value);

        // Username
        $Form->field['username'] = $Form->input(array
        (
            'name'          => 'username',
            'type'          => 'text',
            'max'           => 30,
            'value'         => '',
            'placeholder'   => 'username',
            'css'           => '',
        ));

        // Submit
        $Form->field['submit'] = $Form->submit(array
        (
            'name'      => 'submit',
            'css'       => '',
        ));

        // Set
        $this->Form = $Form;
    }

    // Get Forums
    final private function getForums(&$db)
    {
        $method = 'AdminForumAdminsPage->getForums()';

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Forums
        $sql = 'SELECT id, name
            FROM forums
            WHERE isforum=1
            AND isdeleted=0
            ORDER BY forum_order
            DESC';
        $this->forums_result = $db->sql_query($sql);
        $this->forums_rownum = $db->sql_numrows($this->forums_result);
    }

    // Get Forum Administrators
    final private function getForumAdmins(&$db,&$Member)
    {
        // Method
        $method = 'AdminForumAdminsPage->getForumAdmins()';

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Admins
        $sql = 'SELECT id, user_id, forum_id, lock_thread, lock_post, bump_thread, move_thread, sticky_thread,
            edit_thread, edit_post
            FROM forum_admins
            ORDER BY forum_id 
            ASC';
        $this->f_admin_result   = $db->sql_query($sql);
        $this->f_admin_rownum   = $db->sql_numrows($this->f_admin_result);

        // Add Member
        $Member->idAddRows($this->f_admin_result,'user_id');
    }
}