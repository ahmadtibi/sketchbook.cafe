<?php

class ProfileInfoSubmit
{
    // Construct
    public function __construct()
    {
        // Globals
        global $db,$User;

        // Classes
        sbc_class('Message');


        // === Do this last!

        // Open Connection
        $db->open();

        // User Required
        $User->required($db);

        // Close Connection
        $db->close();

        error('end of submit post');
    }
}