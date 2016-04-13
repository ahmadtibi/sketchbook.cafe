<?php

class ProfileInfoSubmit
{
    // Construct
    public function __construct()
    {
        // Globals
        global $db,$User;

        // Classes
        sbc_class('UserTimer');
        sbc_class('Message');

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

        // Set SQL Vars
        $title      = $titleObject->getMessage();
        $title_code = $titleObject->getMessageCode();

        // === Do this last!

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
            title_code=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ssi',$title,$title_code,$user_id);
        if (!$stmt->execute())
        {
            error('Could not execute statement (update user information) for ProfileInfoSubmit->construct()');
        }
        $stmt->close();

        // User Timer
        $UserTimer->update($db);

        // Close Connection
        $db->close();

        // Header
        header('Location: https://www.sketchbook.cafe/settings/info/');
        exit;
    }
}