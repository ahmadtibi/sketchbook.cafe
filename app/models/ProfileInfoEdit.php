<?php

class ProfileInfoEdit
{
    public $form;

    // Consruct
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
        $user_id        = $User->getUserId();
        $ProcessAllData = new ProcessAllData();

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get User Information
        $sql = 'SELECT title_code
            FROM users
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$user_id);
        if (!$stmt->execute())
        {
            error('Could not execute statement (get user information) for Settings->info()');
        }
        $result = $stmt->get_result();
        $row    = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        $stmt->close();

        // Close Connection
        $db->close();

        // Set vars
        $title_code = $row['title_code'];

        // New Form
        $Form = new Form(array
        (
            'name'      => 'infoform',
            'action'    => 'https://www.sketchbook.cafe/settings/info_submit/',
            'method'    => 'POST',
        ));

        // Submit
        $Form->field['submit'] = $Form->submit(array
        (
            'name'      => 'submit',
            'css'       => '',
        ));

        // User Title
        $Form->field['title'] = $Form->input(array
        (
            'name'          => 'title',
            'type'          => 'text',
            'max'           => 50,
            'value'         => $title_code,
            'placeholder'   => 'title',
            'css'           => 'input300',
        ));

        // Set vars
        $this->form = $Form;
    }
}