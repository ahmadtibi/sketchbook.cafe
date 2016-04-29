<?php
// @author          Jonathan Maltezo (Kameloh)
// @lastUpdated     2016-04-27
// HomePage
class HomePage
{
    // Construct
    public function __construct(&$obj_array)
    {
        // Initialize Objects
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // Open Connection
        $db->open();

        // User Optional
        $User->setFrontpage();
        $User->optional($db);

        // Process Data
        $ProcessAllData = new ProcessAllData();

        // Close Connection
        $db->close();
    }
}