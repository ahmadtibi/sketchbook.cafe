<?php

class AdminForumCategoriesEditSubmit
{
    // Category ID
    private $id = 0;
    private $user_id = 0;
    private $ip_address = '';

    private $category = '';
    private $category_code = '';
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

        // Category ID
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        if ($id < 1)
        {
            error('Category ID is not set for AdminForumCategoriesEditSubmit->construct()');
        }
        $this->id = $id;

        // Category
        $CategoryObject = new Message(array
        (
            'name'          => 'category',
            'min'           => 1,
            'column_max'    => 250,
            'nl2br'         => 0,
            'basic'         => 0,
            'ajax'          => 0,
            'images'        => 0,
            'videos'        => 0,
        ));
        $CategoryObject->insert($_POST['category']);

        // Textarea Settings
        $TextareaSettings = new TextareaSettings('admin_forum_category_description');
        $TextareaSettings->setValue('');
        $message_settings   = $TextareaSettings->getSettings();

        // Description
        $DescriptionObject  = new Message($message_settings);
        $DescriptionObject->insert($_POST['description']);

        // Set SQL Vars
        $this->category         = $CategoryObject->getMessage();
        $this->category_code    = $CategoryObject->getMessageCode();
        $this->description      = $DescriptionObject->getMessage();
        $this->description_code = $DescriptionObject->getMessageCode();

        // Open Connection
        $db->open();

        // Admin Required + Process Data
        $User->setFrontpage();
        $User->admin($db);
        $User->requireAdminFlag('manage_forum_categories');
        $this->user_id = $User->getUserId();

        // Get Category Information
        $this->getCategoryInfo($db);

        // Update Category
        $this->updateCategory($db);

        // Close Connection
        $db->close();

        // Header
        header('Location: https://www.sketchbook.cafe/admin/forum_categories/');
        exit;
    }

    // Get Category Information
    final private function getCategoryInfo(&$db)
    {
        // Initialize Vars
        $id = $this->id;

        // Check just in case
        if ($id < 1)
        {
            error('Dev error: $id is not set for AdminForumCategoriesEditSubmit->getCategoryInfo()');
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Category Information
        $sql = 'SELECT id, iscategory
            FROM forums
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$id);
        if (!$stmt->execute())
        {
            error('Could not execute statement (get category info) for AdminForumCategoriesEditSubmit->getCategoryInfo()');
        }
        $result = $stmt->get_result();
        $row    = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        $stmt->close();

        // Check
        $id = isset($row['id']) ? (int) $row['id'] : 0;
        if ($id < 1)
        {
            error('Could not find category in database for AdminForumCategoriesEditSubmit->getCategoryInfo()');
        }

        // Make sure it's a category
        if ($row['iscategory'] != 1)
        {
            error('ID is not a category.');
        }
    }

    // Update Category
    final private function updateCategory(&$db)
    {
        // Initialize Vars
        $id                 = $this->id;
        $name               = $this->category;
        $name_code          = $this->category_code;
        $description        = $this->description;
        $description_code   = $this->description_code;

        // Just in case
        if ($id < 1)
        {
            error('Dev error: $id is not set for AdminForumCategoriesEditSubmit->updateCategory()');
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
            error('Could not execute statement (update category) for AdminForumCategoriesEditSubmit->updateCategory()');
        }
        $stmt->close();
    }
}