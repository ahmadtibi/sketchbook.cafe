<?php
// @author          Kameloh
// @lastUpdated     2016-05-22

/*
use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\Form\Form as Form;

class StreamsAddStreamPage
{
    private $sketch_points = 0;
    private $sketch_points_required = 200; // required to add stream
    private $user_id = 0;
    private $Form = [];

    public function __construct(&$obj_array)
    {
        $method = 'StreamsAddStreamPage->__construct()';

        // Initialize
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // Open Connection
        $db->open();

        // User Required
        $User->setFrontpage();
        $User->findColumn('sketch_points');
        $User->required($db);
        $this->user_id = $User->getUserId();
        $this->sketch_points = (int) $User->getColumn('sketch_points');

        // Points check
        if ($this->sketch_points < $this->sketch_points_required)
        {
            SBC::userError('Sorry, you must have at least '.$this->sketch_points_required.' sketch points
                to add a stream');
        }

        // Check if the user already has a stream
        $this->checkStream($db);

        // Process Data
        $ProcessAllData = new ProcessAllData();

        // Close Connection
        $db->close();

        // Create Form
        $this->createForm();
    }

    // Check if the user has a stream
    final private function checkStream(&$db)
    {
        $method = 'StreamsAddStreamPage->checkStream()';

        // Initialize
        $user_id    = $this->user_id;
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
            SBC::userError('Sorry, your stream is already listed');
        }
    }

    // Create Form
    final private function createForm()
    {
        $method = 'StreamsAddStreamPage->createForm()';

        // New Form
        $Form = new Form(array
        (
            'name'      => 'streamapplyform',
            'action'    => 'https://www.sketchbook.cafe/streams/add_stream_submit/',
            'method'    => 'POST',
        ));

        // Twitch Username
        $Form->field['username'] = $Form->input(array
        (
            'name'          => 'name',
            'type'          => 'text',
            'max'           => 50,
            'value'         => '',
            'placeholder'   => 'username at twitch',
            'css'           => 'input300',
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

    // Get Form
    final public function getForm()
    {
        return $this->Form;
    }
}
*/