<?php
// Mailbox Page
class MailboxPage
{
    // Construct
    public function __construct()
    {
        // Globals
        global $db,$User;

        // Open Connection
        $db->open();

        // User Required
        $User->required($db);
        $ProcessAllData = new ProcessAllData();

        // Close Connection
        $db->close();
    }
}