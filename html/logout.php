<?php
echo 'Page is obsolete! Please refer to: <a href="https://www.sketchbook.cafe/logout/">https://www.sketchbook.cafe/logout/</a>';
exit;

/*
// Logout page
define('BOOT',1);

// Functions + Classes
require '../app/functions/error.php';
require '../app/functions/sbc_function.php';
require '../app/classes/Db.php';
require '../app/classes/User.php';

// Vars
$ip_address = $_SERVER['REMOTE_ADDR'];
$time       = time();

// Create User Object
$User = new User();

// Database Object
require '../app/database_settings.php';
$db = new Db($database_settings);
unset($database_settings); // just in case

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
$db->open();
$User->logout($db);
$db->close();


// Header
header('Location: https://www.sketchbook.cafe');
exit;
*/