<?php
// @author          Kameloh
// @lastUpdated     2016-05-21

use SketchbookCafe\SBC\SBC as SBC;

class PrivacyPage
{
    public function __construct(&$obj_array)
    {
        $method = 'PrivacyPage->__construct()';

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