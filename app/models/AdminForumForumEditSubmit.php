<?php

class AdminForumForumEditSubmit
{
    // Category ID
    private $id = 0;
    private $user_id = 0;
    private $ip_address = '';

    private $name = '';
    private $name_code = '';
    private $description = '';
    private $description_code = '';

    // Construct
    public function __construct(&$obj_array)
    {
        // Initialize Objects
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // Classes and Functions
        sbc_class('Message');
        sbc_class('TextareaSettings');

        // Initialize Vars
        $this->ip_address   = $_SERVER['REMOTE_ADDR'];

        // Forum ID
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        if ($id < 1)
        {
            error('Forum ID is not set for AdminForumForumEditSubmit->construct()');
        }
        $this->id = $id;

        // Forum
        $ForumObject = new Message(array
        (
            'name'          => 'name',
            'min'           => 1,
            'column_max'    => 250,
            'nl2br'         => 0,
            'basic'         => 0,
            'ajax'          => 0,
            'images'        => 0,
            'videos'        => 0,
        ));
        $ForumObject->insert($_POST['name']);

        // Textarea Settings
        $TextareaSettings = new TextareaSettings('admin_forum_forum_description');
        $TextareaSettings->setValue('');
        $message_settings   = $TextareaSettings->getSettings();

        // Description
        $DescriptionObject  = new Message($message_settings);
        $DescriptionObject->insert($_POST['description']);

        // Set SQL Vars
        $this->name             = $ForumObject->getMessage();
        $this->name_code        = $ForumObject->getMessageCode();
        $this->description      = $DescriptionObject->getMessage();
        $this->description_code = $DescriptionObject->getMessageCode();

        // Open Connection
        $db->open();

        // Admin Required + Process Data
        $User->admin($db);
        $User->requireAdminFlag('manage_forum_forums');
        $this->user_id = $User->getUserId();

        // Get Forum Information
        $this->getForumInfo($db);

        // Update Forum
        $this->updateForum($db);

        // Close Connection
        $db->close();

        // Header
        header('Location: https://www.sketchbook.cafe/admin/forum_forums/');
        exit;
    }

    // Get Forum Information
    final private function getForumInfo(&$db)
    {
        // Initialize Vars
        $id = $this->id;

        // Check just in case
        if ($id < 1)
        {
            error('Dev error: $id is not set for AdminForumForumEditSubmit->getForumInfo()');
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Category Information
        $sql = 'SELECT id, isforum
            FROM forums
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$id);
        if (!$stmt->execute())
        {
            error('Could not execute statement (get forum info) for AdminForumForumEditSubmit->getForumInfo()');
        }
        $result = $stmt->get_result();
        $row    = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        $stmt->close();

        // Check
        $id = isset($row['id']) ? (int) $row['id'] : 0;
        if ($id < 1)
        {
            error('Could not find forum in database for AdminForumForumEditSubmit->getForumInfo()');
        }

        // Make sure it's a forum
        if ($row['isforum'] != 1)
        {
            error('ID is not a forum.');
        }
    }

    // Update Forum
    final private function updateForum(&$db)
    {
        // Initialize Vars
        $id                 = $this->id;
        $name               = $this->name;
        $name_code          = $this->name_code;
        $description        = $this->description;
        $description_code   = $this->description_code;

        // Just in case
        if ($id < 1)
        {
            error('Dev error: $id is not set for AdminForumForumEditSubmit->updateForum()');
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Update Category
        $sql = 'UPDATE forums
            SET name=?,
            name_code=?,
            description=?,
            description_code=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ssssi',$name,$name_code,$description,$description_code,$id);
        if (!$stmt->execute())
        {
            error('Could not execute statement (update forum) for AdminForumForumEditSubmit->updateForum()');
        }
        $stmt->close();
    }
}