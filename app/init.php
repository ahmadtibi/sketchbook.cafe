<?php
use SketchbookCafe\Db\Db as Db;

// Functions + Classes
require 'functions/error.php';
require 'functions/sbc_function.php';
require 'functions/sbc_class.php';
require 'classes/ProcessAllData.php';
require 'classes/Db.php';
require 'classes/User.php';
require 'classes/Member.php';

// Vars
$sbc_function['test']           = 1;
$sbc_class['test']              = 1;

// Initialize Objects
$User   = new User();
$Member = new Member();

// Database Object
require 'database_settings.php';
$db = new Db($database_settings);
unset($database_settings); // just in case

// Composer AutoLoader
require '../vendor/autoload.php';

// Core
require 'core/App.php';
require 'core/Controller.php';