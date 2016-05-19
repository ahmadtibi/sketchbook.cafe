<?php
// @author          Kameloh
// @lastUpdated     2016-05-17

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\Form\Form as Form;

class ChallengesApplyPage
{
    private $Form;

    public function __construct(&$obj_array)
    {
        $method = 'ChallengesApplyPage->__construct()';

        // Initialize
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // Open Connection
        $db->open();

        // User Required + Frontpage
        $User->setFrontpage();
        $User->required($db);

        // Process Data
        $ProcessAllData = new ProcessAllData();

        // Close Connection
        $db->close();

        // Create Apply Form
        $this->createForm();
    }

    // Create Form
    final private function createForm()
    {
        $method = 'ChallengesApplyPage->Form()';

        // New Form
        $Form = new Form(array
        (
            'name'      => 'applyforchallenge',
            'action'    => '',
            'method'    => 'POST',
        ));

        // Hidden
        $Form->field['hidden'] = $Form->hidden(array
        (
            'name'      => 'hidden',
            'value'     => 1,
        ));

        // Submit
        $Form->field['submit'] = $Form->submit(array
        (
            'name'      => 'Submit',
            'css'       => '',
        ));

        // Set
        $this->Form = $Form;
    }

    // Get Form
    final public function getForm()
    {
        return $this->Form;
    }
}