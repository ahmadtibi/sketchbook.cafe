<?php
// Settings Page

class Settings extends Controller
{
    public function __construct()
    {

    }

    // Main page
    public function index()
    {
        $this->view('settings/index');
    }

    // Avatars
    public function avatar()
    {
        // Classes
        sbc_class('Form');

        // New Form
        $Form = new Form(array
        (
            'name'      => 'avatarform',
            'action'    => 'https://www.sketchbook.cafe/settings/avatar/submit/',
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
            'post_url'  => 'https://www.sketchbook.cafe/settings/avatar/submit/',
            'css'       => '',
        ));

        $this->view('settings/avatar', ['Form' => $Form]);
    }
}