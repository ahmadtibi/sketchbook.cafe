<?php
// Logout page
define('BOOT',1);

// Functions + Classes
require '../app/functions/error.php';
require '../app/classes/Db.php';

// Vars
$ip_address = $_SERVER['REMOTE_ADDR'];
$time       = time();

// Set Vars
$cookie_path    = '/';
$cookie_domain  = '.sketchbook.cafe';
$cookie_life    = 5184000;
$cookie_time    = $time + $cookie_life;
$https          = true;
$http_only      = true;

// Remove Cookies
setcookie('id','',$cookie_time,$cookie_path,$cookie_domain,$https,$http_only);
setcookie('rd','',$cookie_time,$cookie_path,$cookie_domain,$https,$http_only);
setcookie('session_id','',$cookie_time,$cookie_path,$cookie_domain,$https,$http_only);
setcookie('session_code','',$cookie_time,$cookie_path,$cookie_domain,$https,$http_only);

// Fully Remove Session if Valid



// Header
header('Location: https://www.sketchbook.cafe');
exit;