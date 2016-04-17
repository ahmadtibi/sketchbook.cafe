<?php
// Mailbox Page
class MailboxPage
{
    // Construct
    public function __construct(&$obj_array)
    {
        // Initialize Objects
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // Open Connection
        $db->open();

        // User Required
        $User->required($db);
        $ProcessAllData = new ProcessAllData();

        // Close Connection
        $db->close();
    }
}