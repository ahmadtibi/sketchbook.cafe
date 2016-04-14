<?php

class AdminLoginPage
{
    public $form;

    // Construct
    public function __construct()
    {
        // Classes and Functions
        sbc_class('Form');

        // Globals
        global $db,$User;

        // Open Connection
        $db->open();

        // Required User + Process Data
        $User->required($db);
        $ProcessAllData = new ProcessAllData();
        if (!$User->isAdmin())
        {
            error('Sorry, only administrators may access this area');
        }

        // Close Connection
        $db->close();

        // New Form
        $Form = new Form(array(
            'name'      => 'adminloginform',
            'action'    => 'https://www.sketchbook.cafe/adminlogin/submit/',
            'method'    => 'POST',
        ));

        // Submit
        $Form->field['submit'] = $Form->submit(array
        (
            'name'  => 'submit',
            'css'   => '',
        ));

        // Password 1
        $Form->field['pass1'] = $Form->input(array
        (
            'name'          => 'pass1',
            'type'          => 'password',
            'max'           => 100,
            'placeholder'   => 'password 1',
        ));

        // Password 2
        $Form->field['pass2'] = $Form->input(array
        (
            'name'          => 'pass2',
            'type'          => 'password',
            'max'           => 100,
            'placeholder'   => 'password 2',
        ));

        // Password 3
        $Form->field['pass3'] = $Form->input(array
        (
            'name'          => 'pass3',
            'type'          => 'password',
            'max'           => 100,
            'placeholder'   => 'password 3',
        ));

        // Set Vars
        $this->form = $Form;
    }
}