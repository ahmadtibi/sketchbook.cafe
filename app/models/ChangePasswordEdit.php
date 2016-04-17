<?php

class ChangePasswordEdit
{
    public $form;

    // Construct
    public function __construct(&$obj_array)
    {
        // Initialize Objects
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // Classes and Functions
        sbc_class('Form');

        // Open Connection
        $db->open();

        // Required User + Process Data
        $User->required($db);
        $ProcessAllData = new ProcessAllData();

        // Close Connection
        $db->close();

        // New Form
        $Form = new Form(array
        (
            'name'      => 'changepasswordform',
            'action'    => 'https://www.sketchbook.cafe/settings/changepassword_submit/',
            'method'    => 'POST',
        ));

        // Password 1
        $Form->field['pass1'] = $Form->input(array
        (
            'name'          => 'pass1',
            'type'          => 'password',
            'max'           => 100,
            'placeholder'   => 'new password',
        ));

        // Password 2
        $Form->field['pass2'] = $Form->input(array
        (
            'name'          => 'pass2',
            'type'          => 'password',
            'max'           => 100,
            'placeholder'   => 'new password',
        ));

        // Confirm Password
        $Form->field['current_password'] = $Form->input(array
        (
            'name'          => 'current_password',
            'type'          => 'password',
            'max'           => 100,
            'placeholder'   => 'current password',
        ));

        // Submit
        $Form->field['submit'] = $Form->submit(array
        (
            'name'      => 'submit',
            'css'       => '',
        ));

        // Set vars
        $this->form = $Form;
    }
}