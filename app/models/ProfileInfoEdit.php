<?php
// @author          Kameloh
// @lastUpdated     2016-05-16
use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\Form\Form as Form;
use SketchbookCafe\TextareaSettings\TextareaSettings as TextareaSettings;

class ProfileInfoEdit
{
    public $form;

    // Consruct
    public function __construct(&$obj_array)
    {
        $method = 'ProfileInfoEdit->__construct()';

        // Set Objects
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // Open Connection
        $db->open();

        // Required User + Process Data
        $User->setFrontpage();
        $User->required($db);
        $user_id = $User->getUserId();

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get User Information
        $sql = 'SELECT title_code, aboutme_code, forumsignature_code 
            FROM users
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$user_id);
        $row  = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Process Data Last
        $ProcessAllData = new ProcessAllData();

        // Close Connection
        $db->close();

        // Set vars
        $title_code             = $row['title_code'];
        $aboutme_code           = $row['aboutme_code'];
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

        // About Me
        $ts_aboutme = new TextareaSettings('aboutme');
        $ts_aboutme->setValue($aboutme_code);
        $Form->field['aboutme'] = $Form->textarea($ts_aboutme->getSettings());

        // Textarea Settings
        $TextareaSettings = new TextareaSettings('forumsignature');
        $TextareaSettings->setValue($forumsignature_code);
        $message_settings = $TextareaSettings->getSettings();

        // Forum Signature
        $Form->field['forumsignature'] = $Form->textarea($message_settings);

        // Set vars
        $this->form = $Form;
    }
}