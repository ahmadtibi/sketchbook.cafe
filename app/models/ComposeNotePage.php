<?php
// @author          Kameloh
// @lastUpdated     2016-04-27

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\Form\Form as Form;
use SketchbookCafe\TextareaSettings\TextareaSettings as TextareaSettings;

// Compose Note Page
class ComposeNotePage
{
    public $form = '';

    // Construct
    public function __construct(&$obj_array)
    {
        // Initialize Objects
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // Open Connection
        $db->open();

        // User Required
        $User->setFrontpage();
        $User->required($db);

        // Process All Data
        $ProcessAllData = new ProcessAllData();

        // Close Connection
        $db->close();

        // New Form
        $Form   = new Form(array
        (
            'name'      => 'composenoteform',
            'action'    => 'https://www.sketchbook.cafe/mailbox/compose_submit/',
            'method'    => 'POST',
        ));

        // Username (recipient)
        $Form->field['username'] = $Form->input(array
        (
            'name'          => 'username',
            'type'          => 'text',
            'max'           => 30,
            'value'         => '',
            'placeholder'   => 'username',
            'css'           => '',
        ));

        // Title
        $Form->field['title'] = $Form->input(array
        (
            'name'          => 'title',
            'type'          => 'text',
            'max'           => 100, 
            'value'         => '',
            'placeholder'   => 'title',
            'css'           => 'input300',
        ));

        // Textarea Settings
        $TextareaSettings   = new TextareaSettings('composenote');
        $TextareaSettings->setValue('');
        $message_settings   = $TextareaSettings->getSettings();

        // Message
        $Form->field['message'] = $Form->textarea($message_settings);

        // Set vars
        $this->form = $Form;
    }
}