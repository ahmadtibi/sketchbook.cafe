<?php

class ChangeEmailEdit
{
    public $form;
    public $current_email;

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
        $User->setFrontpage();
        $User->required($db);
        $user_id        = $User->getUserId();
        $ProcessAllData = new ProcessAllData();

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get user's e-mail
        $sql = 'SELECT email
            FROM users
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$user_id);
        if (!$stmt->execute())
        {
            error('Could not execute statement (get user\'s e-mail) for ChangeEmailEdit->construct()');
        }
        $result = $stmt->get_result();
        $row    = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        $stmt->close();

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