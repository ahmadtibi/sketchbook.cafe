<?php
// @author          Kameloh
// @lastUpdated     2016-04-26
use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\GetSettingName\GetSettingName as GetSettingName;
use SketchbookCafe\Db\Db as Db;
use SketchbookCafe\User\User as User;
use SketchbookCafe\Member\Member as Member;
use SketchbookCafe\Comment\Comment as Comment;

use SketchbookCafe\UserTimer\UserTimer as UserTimer;
use SketchbookCafe\TextareaSettings\TextareaSettings as TextareaSettings;
use SketchbookCafe\Message\Message as Message;

// Composer AutoLoader
require '../vendor/autoload.php';

// Functions + Classes
require 'functions/error.php';

// Initialize Objects
$User = new User();

// Database Object
require 'database_settings.php';
$db = new Db($database_settings);
unset($database_settings); // just in case

// == Comment Previews ==================================================

// Get Setting Name
$nameObj        = new GetSettingName($_POST['setting_name']);
$setting_name   = $nameObj->getValue();

// Textarea Settings
$TextareaSettings   = new TextareaSettings($setting_name);
$TextareaSettings->setValue('');
$TextareaSettings->setAjax(1); // comment preview only!
$message_settings   = $TextareaSettings->getSettings();

// New Message
$MessageObject = new Message($message_settings);
$MessageObject->insert($_POST['message']);

// Set vars
$message    = $MessageObject->getMessage();

// Open Connection
$db->open();

// User Required
$User->required($db);
$user_id = $User->getUserId();

// User Timer
$UserTimer = new UserTimer(array
(
    'user_id'   => $user_id,
));
$UserTimer->setColumn('action');
$UserTimer->checkTimer($db);

// Update User Timer
$UserTimer->update($db);

// Close Connection
$db->close();
?>
<div class="preview_wrap">
    <div class="preview_title sbc_font sbc_font_size">
        Message Preview
    </div>
    <div class="preview_message sbc_font sbc_font_size sbc_font_height sbc_font_link">
        <?php echo $message;?>
    </div>
</div>