<?php
// @author          Kameloh
// @lastUpdated     2016-04-27

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\Message\Message as Message;
use SketchbookCafe\TextareaSettings\TextareaSettings as TextareaSettings;

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
        $method = 'AdminForumCategoriesEditSubmit->__construct()';

        // Initialize Objects
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // Initialize Vars
        $this->ip_address   = SBC::getIpAddress();

        // Category ID
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        if ($id < 1)
        {
            SBC::devError('Category ID is not set',$method);
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
        $User->admin($db);
        $User->requireAdminFlag('manage_forum_forums');
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
        $method = 'AdminForumCategoriesEditSubmit->getCategoryInfo()';

        // Initialize Vars
        $id = $this->id;

        // Check just in case
        if ($id < 1)
        {
            SBC::devError('$id is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Category Information
        $sql = 'SELECT id, iscategory
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
            SBC::devError('Could not find category in database',$method);
        }

        // Make sure it's a category
        if ($row['iscategory'] != 1)
        {
            SBC::devError('ID is not a category',$method);
        }
    }

    // Update Category
    final private function updateCategory(&$db)
    {
        $method = 'AdminForumCategoriesEditSubmit->updateCategory()';

        // Initialize Vars
        $id                 = $this->id;
        $name               = $this->category;
        $name_code          = $this->category_code;
        $description        = $this->description;
        $description_code   = $this->description_code;

        // Just in case
        if ($id < 1)
        {
            SBC::devError('$id is not set',$method);
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
        SBC::statementExecute($stmt,$db,$sql,$method);
    }
}