<?php

class SettingsPage
{
    // Construct
    public function __construct()
    {
        // Globals
        global $db,$User;

        // Open Connection
        $db->open();

        // Required User + Process Data
        $User->required($db);
        $ProcessAllData = new ProcessAllData();

        // Close Connection
        $db->close();
    }
}