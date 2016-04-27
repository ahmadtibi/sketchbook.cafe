<?php
define('BOOT',1);
require_once('../app/init.php');

$http = $_SERVER['HTTP_CF_CONNECTING_IP'];


$address = $_SERVER['REMOTE_ADDR'];
if ($address != '199.27.133.10' || $http != '72.199.65.245')
{
    echo $address;
    echo 'Under construction!';
    exit;
}
$obj_array = array
(
    'db'        => $db,
    'User'      => $User,
    'Comment'   => $Comment,
    'Member'    => $Member,
);
$App = new App($obj_array);
?>