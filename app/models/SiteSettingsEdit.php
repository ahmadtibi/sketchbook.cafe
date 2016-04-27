<?php
// @author          Jonathan Maltezo (Kameloh)
// @lastUpdated     2016-04-26 

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\Form\Form as Form;
use SketchbookCafe\SBCTimezone\SBCTimezone as SBCTimezone;

class SiteSettingsEdit
{
    public $form;

    // Consruct
    public function __construct(&$obj_array)
    {
        // Initialize Objects
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // Open Connection
        $db->open();

        // Required User
        $User->setFrontpage();
        $User->required($db);

        // Process Data
        $ProcessAllData = new ProcessAllData();

        // Close Connection
        $db->close();

        // Create a Timezone Array
        $timezone_list = [];
        $i = 1;
        while ($i < 127)
        {
            $temp_name                  = SBCTimezone::timezone($i,1);
            $timezone_list[$temp_name]  = $i;
            
            $i++;
        }

        // User's Current Timezone ID
        $user_timezone_id = $User->getColumn('timezone_id');

        // New Form
        $Form = new Form(array
        (
            'name'      => 'sitesettingsform',
            'action'    => 'https://www.sketchbook.cafe/settings/sitesettings_submit/',
            'method'    => 'POST',
        ));

        // Submit
        $Form->field['submit'] = $Form->submit(array
        (
            'name'      => 'submit',
            'css'       => '',
        ));

        // Timezone
        $timezone_input = array
        (
            'name'  => 'timezone_id',
        );
        $Form->field['timezone'] = $Form->dropdown($timezone_input,$timezone_list,$user_timezone_id);

        // Set vars
        $this->form = $Form;
    }
}
