<?php
// @author          Jonathan Maltezo (Kameloh)
// @lastUpdated     2016-04-27

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\Form\Form as Form;
use SketchbookCafe\TextareaSettings\TextareaSettings as TextareaSettings;

class AdminForumForumEditPage
{
    private $id = 0;
    public $Form;
    private $name_code = '';
    private $description_code = '';

    // Construct
    public function __construct()
    {
        $method = 'AdminForumForumEditPage->__construct()';
    }

    // Set ID
    final public function setId($id)
    {
        $method = 'AdminForumForumEditPage->setId()';

        // Set ID
        $this->id = isset($id) ? (int) $id : 0;
        if ($this->id < 1)
        {
            SBC::devError('$id is not set',$method);
        }
    }

    // Process
    final public function process(&$obj_array)
    {
        $method = 'AdminForumForumEditPage->process()';

        // Initialize Objects
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];
        $id     = $this->id;
        if ($id < 1)
        {
            SBC::devError('$id is not set',$method);
        }

        // Open Connection
        $db->open();

        // Admin Required + Process Data
        $User->setFrontpage();
        $User->admin($db);
        $User->requireAdminFlag('manage_forum_forums');

        // Get Forum Information
        $this->getForumInfo($db);

        // Close Connection
        $db->close();

        // Set Vars
        $name_code          = $this->name_code;
        $description_code   = $this->description_code;

        // New Form
        $Form   = new Form(array
        (
            'name'      => 'editforumform',
            'action'    => 'https://www.sketchbook.cafe/admin/forum_forums_edit_submit/',
            'method'    => 'POST',
        ));

        // ID
        $Form->field['id']  = $Form->hidden(array
        (
            'name'      => 'id',
            'value'     => $id,
        ));

        // Forum Name
        $Form->field['name'] = $Form->input(array
        (
            'name'          => 'name',
            'type'          => 'text',
            'max'           => 50,
            'value'         => $name_code,
            'placeholder'   => 'forum name',
            'css'           => 'input300',
        ));

        // Textarea Settings
        $TextareaSettings = new TextareaSettings('admin_forum_forum_description');
        $TextareaSettings->setValue($description_code);
        $message_settings   = $TextareaSettings->getSettings();

        // Category Description
        $Form->field['description'] = $Form->textarea($message_settings);

        // Set Vars
        $this->Form = $Form;
    }

    // Get Forum Information
    final private function getForumInfo(&$db)
    {
        $method = 'AdminForumForumEditPage->getForumInfo()';

        // Initialize Vars
        $id = $this->id;

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Info
        $sql = 'SELECT id, name_code, description_code, isforum
            FROM forums
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Check
        $id = isset($row['id']) ? (int) $row['id'] : 0;
        if ($id < 1)
        {
            SBC::devError('Could not find forum ('.$id.') in database',$method);
        }

        // Set Vars
        $this->name_code        = $row['name_code'];
        $this->description_code = $row['description_code'];
    }
}