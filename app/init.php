<?php
use SketchbookCafe\Db\Db as Db;

// Functions + Classes
require 'functions/error.php';
require 'functions/sbc_function.php';
require 'functions/sbc_class.php';
require 'classes/Db.php';
require 'classes/User.php';

// Vars
$user_settings['id']            = (int) isset($_COOKIE['id']) ? $_COOKIE['id'] : 0;
$sbc_function['test']           = 1;
$sbc_class['test']              = 1;

// User Object
$User = new User($user_settings);

// Database Object
require 'database_settings.php';
$db = new Db($database_settings);

// Composer AutoLoader
require '../vendor/autoload.php';

// Core
require 'core/App.php';
require 'core/Controller.php';