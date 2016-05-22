<?php
// @author          Kameloh
// @lastUpdated     2016-05-22

class Settings extends Controller
{
    protected $obj_array = '';

    public function __construct(&$obj_array)
    {
        $this->obj_array = &$obj_array;
    }

    // Stream Settings
    public function stream_edit()
    {
        $this->model('SettingsStreamSubmit',$this->obj_array);
    }
    public function stream()
    {
        // Model
        $Page = $this->model('SettingsStreamPage',$this->obj_array);
        $stream_data = $Page->getStreamData();

        // Vars
        $settings_page = 'stream';

        // View
        $this->view('sketchbookcafe/header');
        $this->view('sketchbookcafe/settings_top', ['settings_page' => $settings_page,]);
        $this->view('settings/stream',
        [
            'stream_data'   => &$stream_data,
        ]);
        $this->view('sketchbookcafe/settings_bottom');
        $this->view('sketchbookcafe/footer');
    }

    // Main page
    public function index()
    {
        // Model
        $this->model('SettingsPage',$this->obj_array);

        // Settings Page
        $settings_page  = 'indexpage';

        // View
        $this->view('sketchbookcafe/header');
        $this->view('sketchbookcafe/settings_top', ['settings_page' => $settings_page,]);
        $this->view('settings/index');
        $this->view('sketchbookcafe/settings_bottom');
        $this->view('sketchbookcafe/footer');
    }

    // Unblock User
    public function unblock($r_user_id = 0)
    {
        $r_user_id  = isset($r_user_id) ? (int) $r_user_id : 0;
        if ($r_user_id < 1)
        {
            $r_user_id = 0;
        }

        // Model
        $UnblockUser = $this->model('UnblockUser');
        $UnblockUser->setUserId($r_user_id);
        $UnblockUser->unblockUser($this->obj_array);
    }

    // Block User
    public function blockuser()
    {
        // Model
        $BlockUser      = $this->model('BlockUserEdit',$this->obj_array);
        $Form           = $BlockUser->form;
        $result         = $BlockUser->result;
        $rownum         = $BlockUser->rownum;

        // Vars
        $settings_page  = 'blockuser';

        // View
        $this->view('sketchbookcafe/header');
        $this->view('sketchbookcafe/settings_top', ['settings_page' => $settings_page,]);
        $this->view('settings/blockuser', 
        [
            'Form'      => $Form, 
            'result'    => $result,
            'rownum'    => $rownum,
        ]);
        $this->view('sketchbookcafe/settings_bottom');
        $this->view('sketchbookcafe/footer');
    }
    public function blockuser_submit()
    {
        // Model
        $this->model('BlockUserSubmit',$this->obj_array);
    }

    // Admin Setup
    public function adminsetup()
    {
        // Model
        $AdminSetup     = $this->model('AdminSetup',$this->obj_array);
        $Form           = $AdminSetup->form;

        // Settings Page
        $settings_page  = 'adminpassword';

        // View
        $this->view('sketchbookcafe/header');
        $this->view('sketchbookcafe/settings_top', ['settings_page' => $settings_page,]);
        $this->view('settings/adminsetup', ['Form' => $Form]);
        $this->view('sketchbookcafe/settings_bottom');
        $this->view('sketchbookcafe/footer');
    }
    public function adminsetup_submit()
    {
        // Model
        $this->model('AdminSetupSubmit',$this->obj_array);
    }

    // Change Password
    public function changepassword()
    {
        // Model
        $ChangePassword = $this->model('ChangePasswordEdit',$this->obj_array);
        $Form           = $ChangePassword->form;

        // Vars
        $settings_page  = 'changepassword';

        // View
        $this->view('sketchbookcafe/header');
        $this->view('sketchbookcafe/settings_top', ['settings_page' => $settings_page,]);
        $this->view('settings/changepassword', ['Form' => $Form]);
        $this->view('sketchbookcafe/settings_bottom');
        $this->view('sketchbookcafe/footer');
    }
    public function changepassword_submit()
    {
        // Model
        $this->model('ChangePasswordSubmit',$this->obj_array);
    }

    // Change E-mail
    public function changeemail()
    {
        // Model
        $ChangeEmail    = $this->model('ChangeEmailEdit',$this->obj_array);
        $Form           = $ChangeEmail->form;
        $current_email  = $ChangeEmail->current_email;

        // Vars
        $settings_page  = 'changeemail';

        // View
        $this->view('sketchbookcafe/header');
        $this->view('sketchbookcafe/settings_top', ['settings_page' => $settings_page,]);
        $this->view('settings/changeemail', [
            'Form'          => $Form,
            'current_email' => $current_email,
        ]);
        $this->view('sketchbookcafe/settings_bottom');
        $this->view('sketchbookcafe/footer');
    }
    public function changeemail_submit()
    {
        // Model
        $this->model('ChangeEmailSubmit',$this->obj_array);
    }

    // Site Settings
    public function sitesettings()
    {
        // Model
        $SiteSettings   = $this->model('SiteSettingsEdit',$this->obj_array);
        $Form           = $SiteSettings->form;

        // Vars
        $settings_page = 'sitesettings';

        // View
        $this->view('sketchbookcafe/header');
        $this->view('sketchbookcafe/settings_top', ['settings_page' => $settings_page,]);
        $this->view('settings/sitesettings', ['Form' => $Form]);
        $this->view('sketchbookcafe/settings_bottom');
        $this->view('sketchbookcafe/footer');
    }
    public function sitesettings_submit()
    {
        $this->model('SiteSettingsSubmit',$this->obj_array);
    }

    // Profile Information
    public function info()
    {
        // Model
        $ProfileInfo    = $this->model('ProfileInfoEdit',$this->obj_array);
        $Form           = $ProfileInfo->form;

        // Vars
        $settings_page = 'info';

        // View
        $this->view('sketchbookcafe/header');
        $this->view('sketchbookcafe/settings_top', ['settings_page' => $settings_page,]);
        $this->view('settings/info', ['Form' => $Form]);
        $this->view('sketchbookcafe/settings_bottom');
        $this->view('sketchbookcafe/footer');
    }
    public function info_submit()
    {
        $this->model('ProfileInfoSubmit',$this->obj_array);
    }

    // Avatars
    public function avatar()
    {
        // Objects
        $User       = $this->obj_array['User'];

        // Avatar Model
        $AvatarEdit = $this->model('AvatarEdit',$this->obj_array);
        $Form       = $AvatarEdit->form;

        // Vars
        $settings_page  = 'avatar';

        // View
        $this->view('sketchbookcafe/header');
        $this->view('sketchbookcafe/settings_top', ['settings_page' => $settings_page,]);
        $this->view('settings/avatar', ['Form' => $Form, 'User' => $User]);
        $this->view('sketchbookcafe/settings_bottom');
        $this->view('sketchbookcafe/footer');
    }
    public function avatar_submit()
    {
        $this->model('AvatarUpload',$this->obj_array);
    }
}