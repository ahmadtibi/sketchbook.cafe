<?php
define('BOOT',1);
require_once('../app/init.php');

$obj_array = array
(
    'db'        => $db,
    'User'      => $User,
    'Comment'   => $Comment,
    'Member'    => $Member,
    'Images'    => $Images,
);
$App = new App($obj_array);


?>