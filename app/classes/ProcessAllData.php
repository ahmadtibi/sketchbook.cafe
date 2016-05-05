<?php
// Process All data
// Dev note: this is a temporary workaround until I can figure out how to work with this framework correctly
// Dev note: this class ASSUMES that $db is already open

class ProcessAllData
{
    // Construct
    public function __construct()
    {
        // Globals
        global $db, $User, $Member, $Comment, $Images;

        // Add User
        $Member->idAddOne($User->getUserId());

        // Process Members
        $Comment->getComments($db);
        $Images->getImages($db);
        $Member->getUsers($db);
    }
}