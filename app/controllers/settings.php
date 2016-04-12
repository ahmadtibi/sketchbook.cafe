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
        $this->model('SettingsPage');
        $this->view('settings/index');
    }

    // Profile Info
    public function info()
    {
        // Process
        $this->model('SettingsPage');

        // Class
        sbc_class('Form');

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
            'value'         => '',
            'placeholder'   => 'title',
        ));

        $this->view('settings/info', ['Form' => $Form]);
    }

    // Info Submit
    public function info_submit()
    {
        $this->model('ProfileInfoSubmit');
    }

    // Avatar Submit
    public function avatar_submit()
    {
        $this->model('AvatarUpload');
    }

    // Avatars
    public function avatar()
    {
        // Main Process
        $this->model('SettingsPage');

        // Classes
        sbc_class('Form');

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

        $this->view('settings/avatar', ['Form' => $Form]);
    }
}