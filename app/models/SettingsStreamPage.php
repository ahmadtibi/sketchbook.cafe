<?php
// @author          Kameloh
// @lastUpdated     2016-05-22

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\Form\Form as Form;

class SettingsStreamPage
{
    private $user_id = 0;
    private $stream_id = 0;
    private $twitch_username = '';

    private $stream_data = [];
    private $Form = [];

    public function __construct(&$obj_array)
    {
        $method = 'SettingsStreamPage->__construct()';

        // Initialize
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // Open Connection
        $db->open();

        // Required User
        $User->setFrontpage();
        $User->required($db);
        $this->user_id = $User->getUserId();

        // Check if they have a stream available
        $this->getStream($db);

        // Process Data
        $ProcessAllData = new ProcessAllData();

        // Close Connection
        $db->close();

        // Create Form
        $this->createForm();
    }

    // Get Stream
    final private function getStream(&$db)
    {
        $method = 'SettingsStreamPage->getStream()';

        // Initialize
        $user_id = $this->user_id;
        if ($user_id < 1)
        {
            SBC::devError('User ID is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Info
        $sql = 'SELECT id, twitch_username
            FROM streamers
            WHERE user_id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$user_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Check
        $id = isset($row['id']) ? (int) $row['id'] : 0;
        if ($id < 1)
        {
            return null;
        }

        // Set
        $this->stream_id        = $row['id'];
        $this->twitch_username  = $row['twitch_username'];
    }

    // Get Stream Data
    final public function getStreamData()
    {
        $method = 'SettingsStreamPage->getStreamData()';

        $array = array
        (
            'stream_id'         => $this->stream_id,
            'twitch_username'   => $this->twitch_username,
            'StreamForm'        => &$this->Form,
        );

        return $array;
    }

    // Create Form
    final private function createForm()
    {
        $method = 'SettingsStreamPage->createForm()';

        $Form = new Form(array
        (
            'name'      => 'streamform',
            'action'    => 'https://www.sketchbook.cafe/settings/stream_edit/',
            'method'    => 'POST',
        ));

        // Stream ID
        $Form->field['stream_id'] = $Form->hidden(array
        (
            'name'  => 'stream_id',
            'value' => $this->stream_id,
        ));

        // Twitch Username
        $Form->field['username'] = $Form->input(array
        (
            'name'          => 'twitch_username',
            'type'          => 'text',
            'max'           => 50,
            'value'         => $this->twitch_username,
            'placeholder'   => 'username at twitch',
            'css'           => 'input200',
        ));

        // Delete
        $Form->field['deletethis'] = $Form->checkbox(array
        (
            'name'      => 'deletethis',
            'value'     => 1,
            'checked'   => 0,
        ));

        // Submit
        $Form->field['submit'] = $Form->submit(array
        (
            'name'      => 'Submit',
            'css'       => '',
        ));

        // Set
        $this->Form = &$Form;
    }
}