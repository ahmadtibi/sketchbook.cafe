<?php
// @author          Kameloh
// @lastUpdated     2016-05-11

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\Form\Form as Form;
use SketchbookCafe\TextareaSettings\TextareaSettings as TextareaSettings;

class ComposeNoteForm
{
    private $Form = '';

    // Construct
    public function __construct()
    {
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
            'css'           => 'input400',
        ));

        // Title
        $Form->field['title'] = $Form->input(array
        (
            'name'          => 'title',
            'type'          => 'text',
            'max'           => 100, 
            'value'         => '',
            'placeholder'   => 'title',
            'css'           => 'input400',
        ));

        // Textarea Settings
        $TextareaSettings       = new TextareaSettings('composenote');
        $Form->field['message'] = $Form->textarea($TextareaSettings->getSettings());

        // Set vars
        $this->Form = $Form;
    }

    // Get Form
    final public function getForm()
    {
        return $this->Form;
    }
}