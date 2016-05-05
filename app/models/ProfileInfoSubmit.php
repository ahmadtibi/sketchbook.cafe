<?php
// @author          Kameloh
// @lastUpdated     2016-04-26

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\UserTimer\UserTimer as UserTimer;
use SketchbookCafe\TextareaSettings\TextareaSettings as TextareaSettings;
use SketchbookCafe\Message\Message as Message;

class ProfileInfoSubmit
{
    // Construct
    public function __construct(&$obj_array)
    {
        $method = 'ProfileInfoSubmit->__construct()';

        // Set Objects
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // New Message
        $titleObject = new Message(array
        (
            'name'          => 'title',
            'min'           => 0,
            'column_max'    => 250,
            'nl2br'         => 0,
            'basic'         => 0,
            'ajax'          => 0,
            'images'        => 0,
            'videos'        => 0,
        ));
        $titleObject->insert($_POST['title']);

        // Textarea Settings
        $TextareaSettings   = new TextareaSettings('forumsignature');
        $message_settings   = $TextareaSettings->getSettings();

        // Forum Signature
        $forumsignatureObject = new Message($message_settings);
        $forumsignatureObject->insert($_POST['forumsignature']);

        // Set SQL Vars
        $title                  = $titleObject->getMessage();
        $title_code             = $titleObject->getMessageCode();
        $forumsignature         = $forumsignatureObject->getMessage();
        $forumsignature_code    = $forumsignatureObject->getMessageCode();
        
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

        // Update User Information
        $sql = 'UPDATE users
            SET title=?,
            title_code=?,
            forumsignature=?,
            forumsignature_code=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ssssi',$title,$title_code,$forumsignature,$forumsignature_code,$user_id);
        SBC::statementExecute($stmt,$db,$sql,$method);

        // User Timer
        $UserTimer->update($db);

        // Close Connection
        $db->close();

        // Header
        header('Location: https://www.sketchbook.cafe/settings/info/');
        exit;
    }
}