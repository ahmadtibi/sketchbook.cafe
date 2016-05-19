<?php
// @author          Kameloh
// @lastUpdated     2016-05-16

// use SketchbookCafe\SBC\SBC as SBC;

class EntryIndexPage
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

        // Process All Data
        $ProcessAllData = new ProcessAllData();

        // Close Connection
        $db->close();
    }
}