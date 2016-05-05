<?php
// @author          Kameloh
// @lastUpdated     2016-04-27
class SettingsPage
{
    // Construct
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
    }
}