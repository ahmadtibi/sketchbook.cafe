<?php
use SketchbookCafe\Db\Db as Db;

// Require Database Settings
require __DIR__ . '/' .'database_settings.php';

$db = new Db($database_settings);