<?php
// @author          Kameloh
// @lastUpdated     2016-05-22

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\Message\Message as Message;

class StreamsAddSubmit
{
    private $ip_address = '';
    private $time = 0;

    private $user_id = 0;
    private $sketch_points = 0;
    private $sketch_points_required = 200;
    private $twitch_username = '';

    public function __construct(&$obj_array)
    {
        $method = 'StreamsAddSubmit->__construct()';

        // Initialize
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];
        $this->ip_address   = SBC::getIpAddress();
        $this->time         = SBC::getTime();

        // Twitch Username
        $twitchObj  = new Message(array
        (
            'name'          => 'twitch_username',
            'min'           => 1,
            'column_max'    => 250,
        ));
        $twitchObj->insert($_POST['twitch_username']);
        $this->twitch_username = $twitchObj->getMessage();

        // Open Connection
        $db->open();

        // User Required
        $User->findColumn('sketch_points');
        $User->required($db);
        $this->user_id = $User->getUserId();
        $this->sketch_points = (int) $User->getColumn('sketch_points');

        if ($this->sketch_points < $this->sketch_points_required)
        {
            SBC::userError('Sorry, you must have at least '.$this->sketch_points_required.' sketch points
                to add your stream');
        }

        // Check if they already have a stream
        $this->getStreamInfo($db);

        // Add Streamer
        $this->addStream($db);

        // Close Connection
        $db->close();

        // Header
        header('Location: https://www.sketchbook.cafe/');
        exit;
    }

    // Get Stream Info
    final private function getStreamInfo(&$db)
    {
        $method = 'StreamsAddSubmit->getStreamInfo()';

        // Initialize
        $user_id = $this->user_id;
        if ($user_id < 1)
        {
            SBC::devError('User ID is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get streamer info
        $sql = 'SELECT id
            FROM streamers
            WHERE user_id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$user_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Check
        $id = isset($row['id']) ? (int) $row['id'] : 0;
        if ($id > 0)
        {
            SBC::userError('Sorry, you already have a stream listed. You can edit it here {link}');
        }
    }

    // Add Stream
    final private function addStream(&$db)
    {
        $method = 'StreamsAddSubmit->addStream()';

        // Initialize
        $ip_address         = $this->ip_address;
        $time               = $this->time;
        $user_id            = $this->user_id;
        $twitch_username    = $this->twitch_username;
        if ($user_id < 1)
        {
            SBC::devError('User ID is not set',$method);
        }
        if (empty($twitch_username))
        {
            SBC::devError('Twitch Username is not set',$method);
        }

        // Character Check
        if (preg_match('/[^A-Za-z0-9_]/',$twitch_username))
        {
            SBC::userError('Invalid characters for twitch username. Allowed: A-Za-z0-9_');
        }

        // Lowercase
        $twitch_username = strtolower($twitch_username);

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Double check to see if the streamer is already listed
        $sql = 'SELECT id
            FROM streamers
            WHERE twitch_username=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('s',$twtich_username);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // ID?
        $id = isset($row['id']) ? (int) $row['id'] : 0;
        if ($id < 1)
        {
            // Add
            $sql = 'INSERT INTO streamers
                SET user_id=?,
                twitch_username=?,
                date_created=?,
                ip_created=?';
            $stmt = $db->prepare($sql);
            $stmt->bind_param('isis',$user_id,$twitch_username,$time,$ip_address);
            SBC::statementExecute($stmt,$db,$sql,$method);
        }
    }
}