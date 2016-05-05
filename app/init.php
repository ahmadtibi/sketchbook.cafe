<?php
// @author          Kameloh
// @lastUpdated     2016-04-27
use SketchbookCafe\Db\Db as Db;
use SketchbookCafe\User\User as User;
use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\Member\Member as Member;
use SketchbookCafe\Comment\Comment as Comment;
use SketchbookCafe\Images\Images as Images;

// Composer AutoLoader
require '../vendor/autoload.php';

// Functions + Classes
require 'functions/error.php';
// require 'functions/statement_error.php';
require 'functions/display_comment.php';
require 'classes/ProcessAllData.php';

// Initialize Objects
$User       = new User();
$Member     = new Member();
$Comment    = new Comment();
$Member     = new Member();
$SBC        = new SBC();
$Images     = new Images();

// Database Object
require 'database_settings.php';
$db = new Db($database_settings);
unset($database_settings); // just in case

// Core
require 'core/App.php';
require 'core/Controller.php';