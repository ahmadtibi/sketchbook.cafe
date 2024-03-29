<?php
// @author          Kameloh
// @lastUpdated     2016-04-26

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\UserTimer\UserTimer as UserTimer;
use SketchbookCafe\SBCTimezone\SBCTimezone as SBCTimezone;

class SiteSettingsSubmit
{
    // Construct
    public function __construct(&$obj_array)
    {
        // Initialize Objects
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // Timezone ID
        $timezone_id = isset($_POST['timezone_id']) ? (int) $_POST['timezone_id'] : 0;
        if ($timezone_id < 1 || $timezone_id > 126)
        {
            $timezone_id = 6; // Los Angeles!
        }

        // Timezone My
        $timezone_my = SBCTimezone::timezone($timezone_id,0);

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
        SBC::statementExecute($stmt,$db,$sql,$method);

        // User Timer
        $UserTimer->update($db);

        // Close Connection
        $db->close();

        // Header
        header('Location: https://www.sketchbook.cafe/settings/sitesettings/');
        exit;
    }
}