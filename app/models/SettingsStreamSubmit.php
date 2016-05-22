<?php
// @author          Kameloh
// @lastUpdated     2016-05-22

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\Message\Message as Message;

class SettingsStreamSubmit
{
    private $stream_id = 0;
    private $twitch_username = '';

    public function __construct(&$obj_array)
    {
        $method = 'SettingsStreamPage->__construct()';

        // Initialize
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // Stream ID
        $this->stream_id  = isset($_POST['stream_id']) ? (int) $_POST['stream_id'] : 0;
        if ($this->stream_id < 1)
        {
            SBC::devError('Stream ID is not set',$method);
        }

        // Twitch Username
        $twitchObj  = new Message(array
        (
            'name'          => 'twitch_username',
            'min'           => 1,
            'column_max'    => 250,
        ));
        $twitchObj->insert($_POST['twitch_username']);
        $this->twitch_username = $twitchObj->getMessage();

        // Delete This?
        $deletethis = isset($_POST['deletethis']) ? (int) $_POST['deletethis'] : 0;
        if ($deletethis != 1)
        {
            $deletethis = 0;
        }

        // Open Connection
        $db->open();

        // Required User
        $User->required($db);
        $this->user_id = $User->getUserId();

        // Get Stream Info
        $this->getStreamInfo($db);

        // Action
        if ($deletethis != 1)
        {
            $this->updateStream($db);
        }
        else
        {
            $this->deleteStream($db);
        }

        // Close Connection
        $db->close();

        // Header
        header('Location: https://www.sketchbook.cafe/settings/stream/');
        exit;
    }

    // Get Stream Info
    final private function getStreamInfo(&$db)
    {
        $method = 'SettingsStreamSubmit->getStreamInfo()';

        // Initialize
        $user_id    = $this->user_id;
        $stream_id  = $this->stream_id;
        if ($user_id < 1)
        {
            SBC::devError('User ID is not set',$method);
        }
        if ($stream_id < 1)
        {
            SBC::devError('Stream ID is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get info
        $sql = 'SELECT id, user_id
            FROM streamers
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$stream_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Check
        $stream_id  = isset($row['id']) ? (int) $row['id'] : 0;
        if ($stream_id < 1)
        {
            SBC::devError('Could not find stream ID in database',$method);
        }

        // Do they own it?
        if ($user_id != $row['user_id'])
        {
            SBC::userError('Sorry, you may only edit streams that belong to you');
        }
    }

    // Update Stream
    final private function updateStream(&$db)
    {
        $method = 'SettingsStreamSubmit->updateStream()';

        // Initialize
        $stream_id          = $this->stream_id;
        $twitch_username     = $this->twitch_username;
        if ($stream_id < 1)
        {
            SBC::devError('Stream ID is not set',$method);
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

        // Update stream
        $sql = 'UPDATE streamers
            SET twitch_username=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('si',$twitch_username,$stream_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }

    // Delete Stream
    final private function deleteStream(&$db)
    {
        $method = 'SettingsStreamSubmit->deleteStream()';

        // Initialize
        $stream_id  = $this->stream_id;
        if ($stream_id < 1)
        {
            SBC::devError('Stream ID is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Delete
        $sql = 'DELETE FROM streamers
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$stream_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }
}