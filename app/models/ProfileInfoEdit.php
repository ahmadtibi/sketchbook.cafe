<?php

class ProfileInfoEdit
{
    public $form;

    // Consruct
    public function __construct(&$obj_array)
    {
        // Set Objects
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // Classes and Functions
        sbc_class('Form');
        sbc_class('TextareaSettings');

        // Open Connection
        $db->open();

        // Required User + Process Data
        $User->required($db);
        $user_id        = $User->getUserId();
        $ProcessAllData = new ProcessAllData();

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get User Information
        $sql = 'SELECT title_code, forumsignature_code 
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
        $title_code             = $row['title_code'];
        $forumsignature_code    = $row['forumsignature_code'];

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

        // Textarea Settings
        $TextareaSettings = new TextareaSettings('forumsignature');
        $TextareaSettings->setValue($forumsignature_code);
        $message_settings   = $TextareaSettings->getSettings();

        // Forum Signature
        $Form->field['forumsignature'] = $Form->textarea($message_settings);

        // Set vars
        $this->form = $Form;
    }
}