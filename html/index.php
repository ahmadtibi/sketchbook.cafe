<?php
define('BOOT',1);
require_once('../app/init.php');

$address = $_SERVER['REMOTE_ADDR'];
if ($address != '199.27.133.30')
{
    echo 'Under construction!';
    exit;
}
$App = new App();
?>