<?php
use SketchbookCafe\Db\Db as Db;

// Functions + Classes
require 'functions/error.php';
require 'functions/sbc_function.php';
require 'functions/sbc_class.php';
require 'classes/Db.php';
require 'classes/User.php';

// Global Vars
$sbc_function['test']   = 1;
$sbc_class['test']      = 1;

// Initialize Objects
$User = new User();

// Database Object
require 'database_settings.php';
$db = new Db($database_settings);
unset($database_settings); // just in case

// == Comment Previews ==================================================

// Classes + Functions
sbc_class('UserTimer');
sbc_class('Message');
sbc_class('TextareaSettings');
sbc_function('get_setting_name');

// Get Setting Name
$setting_name   = '';
$setting_name   = get_setting_name($_POST['setting_name']);

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
<style type="text/css">
.previewWrap {
    padding: 3px;
    overflow: hidden;
}
.previewTitle {
    padding: 3px;
    font-size: 14px;
    line-height: 20px;
    font-family: Georgia, serif;
    font-weight: bold;
}
.previewMessage {
    padding: 3px;
    font-size: 14px;
    line-height: 20px;
    font-family: Georgia, serif;
}
</style>
<div class="previewWrap">
    <div class="previewTitle">
        Message Preview
    </div>
    <div class="previewMessage">
        <?php echo $message;?>
    </div>
</div>