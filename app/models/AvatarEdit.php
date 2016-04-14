<?php

class AvatarEdit
{
    public $form;

    public function __construct()
    {
        // Classes and Functions
        sbc_class('Form');

        // Globals
        global $db,$User;

        // New Form
        $Form = new Form(array
        (
            'name'      => 'avatarform',
            'action'    => 'https://www.sketchbook.cafe/settings/avatar_submit/',
            'method'    => 'POST',
        ));

        // File Input
        $Form->field['imagefile'] = $Form->file(array
        (
            'name'      => 'imagefile',
        ));

        // File Upload
        $Form->field['upload'] = $Form->upload(array
        (
            'name'      => 'imagefile',
            'imagefile' => 'imagefile',
            'post_url'  => 'https://www.sketchbook.cafe/settings/avatar_submit/',
            'css'       => '',
        ));


        // Open Connection
        $db->open();

        // Required User + Process Data
        $User->required($db);
        $ProcessAllData = new ProcessAllData();

        // Close Connection
        $db->close();

        // Set Vars
        $this->form     = $Form;
    }
}