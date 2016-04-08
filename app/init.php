<?php
use SketchbookCafe\Db\Db as Db;

// Functions + Classes
require 'functions/error.php';
require 'classes/Db.php';

// Database Object
require 'database_settings.php';
$db = new Db($database_settings);

// Composer AutoLoader
require '../vendor/autoload.php';

// Core
require 'core/App.php';
require 'core/Controller.php';