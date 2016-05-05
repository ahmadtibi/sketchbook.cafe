<?php
// @author          Kameloh
// @lastUpdated     2016-04-27

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\Form\Form as Form;

class AdminFixUserTablePage
{
    public $Form = '';

    // Construct
    public function __construct(&$obj_array)
    {
        $method = 'AdminFixUserTablePage->__construct()';

        // Initialize Objects
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // Open Connection
        $db->open();

        // Admin Required + Process Data
        $User->setFrontpage();
        $User->admin($db);
        $User->requireAdminFlag('fix_user_table');

        // Process All
        $ProcessAllData = new ProcessAllData();

        // Close Connection
        $db->close();

        // New Form
        $Form   = new Form(array
        (
            'name'      => 'fixuserform',
            'action'    => 'https://www.sketchbook.cafe/admin/fix_user_table_submit/',
            'method'    => 'POST',
        ));

        // Username
        $Form->field['username']    = $Form->input(array
        (
            'name'          => 'username',
            'type'          => 'text',
            'max'           => 30,
            'value'         => '',
            'placeholder'   => 'username',
            'css'           => '',
        ));

        // Submit
        $Form->field['submit']  = $Form->submit(array
        (
            'name'          => 'Submit',
            'css'           => '',
        ));

        // Set
        $this->Form = $Form;
    }
}