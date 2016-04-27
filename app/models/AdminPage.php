<?php
// @author          Jonathan Maltezo (Kameloh)
// @lastUpdated     2016-04-27
class AdminPage
{
    // Construct
    public function __construct(&$obj_array)
    {
        // Initialize Objects
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // Open Connection
        $db->open();

        // Admin Required
        $User->setFrontpage();
        $User->admin($db);

        // Process Data
        $ProcessAllData = new ProcessAllData();

        // Close Connection
        $db->close();
    }
}