<?php

class AdminSetup
{
    public $form;

    // Construct
    public function __construct()
    {
        // Classes + Functions
        sbc_class('Form');

        // Globals
        global $db,$User;

        // Open Connection
        $db->open();

        // Required User + Process Data
        $User->required($db);
        $user_id        = $User->getUserId();

        // Is Admin?
        if (!$User->isAdmin())
        {
            error('Sorry, only administrators may access this page');
        }

        // Close Connection
        $db->close();

        // New Form
        $Form = new Form(array
        (
            'name'      => 'adminsetupform',
            'action'    => 'https://www.sketchbook.cafe/settings/adminsetup_submit/',
            'method'    => 'POST',
        ));

        // Pass 1
        $Form->field['pass1'] = $Form->input(array
        (
            'name'          => 'pass1',
            'type'          => 'password',
            'max'           => 100,
            'placeholder'   => 'password 1',
        ));

        // Pass 2
        $Form->field['pass2'] = $Form->input(array
        (
            'name'          => 'pass2',
            'type'          => 'password',
            'max'           => 100,
            'placeholder'   => 'password 2',
        ));

        // Pass 3
        $Form->field['pass3'] = $Form->input(array
        (
            'name'          => 'pass3',
            'type'          => 'password',
            'max'           => 100,
            'placeholder'   => 'password 3',
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