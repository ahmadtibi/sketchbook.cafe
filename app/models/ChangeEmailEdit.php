<?php
// @author          Jonathan Maltezo (Kameloh)
// @lastUpdated     2016-04-26

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\Form\Form as Form;

class ChangeEmailEdit
{
    public $form;
    public $current_email;

    // Construct
    public function __construct(&$obj_array)
    {
        $method = 'ChangeEmailEdit->__construct()';

        // Initialize Objects
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // Open Connection
        $db->open();

        // Required User
        $User->setFrontpage();
        $User->required($db);
        $user_id = $User->getUserId();

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get user's e-mail
        $sql = 'SELECT email
            FROM users
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$user_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Process All Data
        $ProcessAllData = new ProcessAllData();

        // Close Connection
        $db->close();

        // Set vars
        $user_email = $row['email'];

        // New Form
        $Form = new Form(array
        (
            'name'      => 'changeemailform',
            'action'    => 'https://www.sketchbook.cafe/settings/changeemail_submit/',
            'method'    => 'POST',
        ));

        // E-mail
        $Form->field['email1']   = $Form->input(array
        (
            'name'          => 'email1',
            'type'          => 'text',
            'max'           => 100,
            'placeholder'   => 'e-mail',
            'value'         => '',
            'css'           => 'input300',
        ));

        // E-mail Again
        $Form->field['email2']   = $Form->input(array
        (
            'name'          => 'email2',
            'type'          => 'text',
            'max'           => 100,
            'placeholder'   => 'e-mail again',
            'value'         => '',
            'css'           => 'input300',
        ));

        // Password
        $Form->field['password'] = $Form->input(array
        (
            'name'          => 'password',
            'type'          => 'password',
            'max'           => 100,
            'placeholder'   => 'password',
        ));

        // Submit
        $Form->field['submit'] = $Form->submit(array
        (
            'name'      => 'submit',
            'css'       => '',
        ));

        // Set vars
        $this->form             = $Form;
        $this->current_email    = $user_email;
    }
}