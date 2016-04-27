<?php
define('BOOT',1);

// Composer AutoLoader
require '../vendor/autoload.php';


use SketchbookCafe\Db2\Db2 as Db2;

class testmanhero
{
    // Construct
    public function __construct()
    {


    // Database Settings
    $database_settings = array
    (
        'host'      => 'localhost', 
        'user'      => 'USER', 
        'password'  => 'PASSWORD', 
        'dbname'    => 'DATABASENAME',
    );

        $db = new Db2($database_settings);
    }
}

$test = new testmanhero();
?>
hi