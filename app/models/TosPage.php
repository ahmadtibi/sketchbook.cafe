<?php
// @author          Kameloh
// @lastUpdated     2016-05-21

use SketchbookCafe\SBC\SBC as SBC;

class TosPage
{
    public function __construct(&$obj_array)
    {
        $method = 'TosPage->__construct()';

        // Initialize
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