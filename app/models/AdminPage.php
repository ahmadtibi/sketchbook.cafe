<?php

class AdminPage
{
    // Construct
    public function __construct()
    {
        // Globals
        global $db,$User;

        // Open Connection
        $db->open();

        // Admin Required + Process Data
        $User->admin($db);
        $ProcessAllData = new ProcessAllData();

        // Close Connection
        $db->close();
    }
}