<?php
// @author          Kameloh
// @lastUpdated     2016-05-07

use Aws\S3\S3Client as S3Client;

class TestmanheroIndex
{
    // Construct
    public function __construct(&$obj_array)
    {
        // Initialize Objects
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // Open Connection
        $db->open();

        // Required User
        $User->setFrontpage();
        $User->required($db);

        // Process Data
        $ProcessAllData = new ProcessAllData();

        require '../app/s3_settings.php';

        $client = S3Client::factory(array
        (
            'profile' => $s3_settings,
        ));

        // Close Connection
        $db->close();
    }
}