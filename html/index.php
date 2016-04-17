<?php
define('BOOT',1);
require_once('../app/init.php');

$address = $_SERVER['REMOTE_ADDR'];
if ($address != '199.27.133.30')
{
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