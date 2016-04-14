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

    // Admin Setup
    public function adminsetup()
    {
        // Model
        $AdminSetup     = $this->model('AdminSetup');
        $Form           = $AdminSetup->form;

        // View
        $this->view('settings/adminsetup', ['Form' => $Form]);
    }
    public function adminsetup_submit()
    {
        // Model
        $this->model('AdminSetupSubmit');
    }

    // Change Password
    public function changepassword()
    {
        // Model
        $ChangePassword = $this->model('ChangePasswordEdit');
        $Form           = $ChangePassword->form;

        // View
        $this->view('settings/changepassword', ['Form' => $Form]);
    }
    public function changepassword_submit()
    {
        // Model
        $this->model('ChangePasswordSubmit');
    }

    // Change E-mail
    public function changeemail()
    {
        // Model
        $ChangeEmail    = $this->model('ChangeEmailEdit');
        $Form           = $ChangeEmail->form;
        $current_email  = $ChangeEmail->current_email;

        // View
        $this->view('settings/changeemail', [
            'Form'          => $Form,
            'current_email' => $current_email,
        ]);
    }
    public function changeemail_submit()
    {
        // Model
        $this->model('ChangeEmailSubmit');
    }

    // Site Settings
    public function sitesettings()
    {
        // Model
        $SiteSettings   = $this->model('SiteSettingsEdit');
        $Form           = $SiteSettings->form;

        // View
        $this->view('settings/sitesettings', ['Form' => $Form]);
    }
    public function sitesettings_submit()
    {
        $this->model('SiteSettingsSubmit');
    }

    // Profile Information
    public function info()
    {
        // Model
        $ProfileInfo    = $this->model('ProfileInfoEdit');
        $Form           = $ProfileInfo->form;

        // View
        $this->view('settings/info', ['Form' => $Form]);
    }
    public function info_submit()
    {
        $this->model('ProfileInfoSubmit');
    }

    // Avatars
    public function avatar()
    {
        // Avatar Model
        $AvatarEdit = $this->model('AvatarEdit');
        $Form       = $AvatarEdit->form;

        // View
        $this->view('settings/avatar', ['Form' => $Form]);
    }
    public function avatar_submit()
    {
        $this->model('AvatarUpload');
    }
}