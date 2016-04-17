<?php

class SiteSettingsSubmit
{
    // Construct
    public function __construct(&$obj_array)
    {
        // Initialize Objects
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // Classes and Functions
        sbc_class('UserTimer');
        sbc_function('sbc_timezone');

        // Timezone ID
        $timezone_id = isset($_POST['timezone_id']) ? (int) $_POST['timezone_id'] : 0;
        if ($timezone_id < 1 || $timezone_id > 126)
        {
            $timezone_id = 6; // Los Angeles!
        }

        // Timezone My
        $timezone_my = sbc_timezone($timezone_id,0);

        // Open Connection
        $db->open();

        // Required User
        $User->required($db);
        $user_id = $User->getUserId();

        // User Timer
        $UserTimer = new UserTimer(array
        (
            'user_id'   => $user_id, 
        ));
        $UserTimer->setColumn('action');
        $UserTimer->checkTimer($db);

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Update User
        $sql = 'UPDATE users
            SET timezone_my=?,
            timezone_id=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('sii',$timezone_my,$timezone_id,$user_id);
        if (!$stmt->execute())
        {
            error('Could not execute statement (update user) for SiteSettingsSubmit->construct()');
        }
        $stmt->close();

        // User Timer
        $UserTimer->update($db);

        // Close Connection
        $db->close();

        // Header
        header('Location: https://www.sketchbook.cafe/settings/sitesettings/');
        exit;
    }
}