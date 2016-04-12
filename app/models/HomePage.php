<?php
// HomePage
class HomePage
{
    // Construct
    public function __construct()
    {
        // Globals
        global $db,$User;

        // Open Connection
        $db->open();

        // User Optional
        $User->optional($db);
        $ProcessAllData = new ProcessAllData();

        // Close Connection
        $db->close();
    }

}