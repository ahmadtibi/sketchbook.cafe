<?php
// @author          Kameloh
// @lastUpdated     2016-05-22

use SketchbookCafe\SBC\SBC as SBC;

class StreamsIndexPage
{
    private $obj_array = [];

    public function __construct(&$obj_array)
    {
        $method = 'StreamsIndexPage->__construct()';

        // Initialize
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