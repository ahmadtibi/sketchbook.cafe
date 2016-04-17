<?php

class SiteSettingsEdit
{
    public $form;

    // Consruct
    public function __construct(&$obj_array)
    {
        // Initialize Objects
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // Classes and Functions
        sbc_class('Form');
        sbc_function('sbc_timezone');

        // Open Connection
        $db->open();

        // Required User + Process Data
        $User->required($db);
        $ProcessAllData = new ProcessAllData();

        // Close Connection
        $db->close();

        // Create a Timezone Array
        $timezone_list = [];
        $i = 1;
        while ($i < 127)
        {
            $temp_name                  = sbc_timezone($i,1);
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
