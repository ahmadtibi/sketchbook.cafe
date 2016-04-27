<?php
// @author          Jonathan Maltezo (Kameloh)
// @lastUpdated     2016-04-27

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\Form\Form as Form;

class AdminLoginPage
{
    public $form;

    // Construct
    public function __construct(&$obj_array)
    {
        $method = 'AdminLoginPage->__construct()';

        // Initialize Objects
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // Open Connection
        $db->open();

        // Required User
        $User->setFrontpage();
        $User->required($db);
        if (!$User->isAdmin())
        {
            SBC::userError('Sorry, only administrators may access this area');
        }

        // Process Data
        $ProcessAllData = new ProcessAllData();

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