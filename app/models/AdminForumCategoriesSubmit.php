<?php
// @author          Kameloh
// @lastUpdated     2016-04-27

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\Message\Message as Message;
use SketchbookCafe\TextareaSettings\TextareaSettings as TextareaSettings;

class AdminForumCategoriesSubmit
{
    // Category ID
    private $id = 0;

    private $user_id = 0;
    private $ip_address = '';

    private $rd = 0;
    private $category = '';
    private $category_code = '';
    private $description = '';
    private $description_code = '';

    // Construct
    public function __construct(&$obj_array)
    {
        $method = 'AdminForumCategoriesSubmit->__construct()';

        // Initialize Objects
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // Initialize Vars
        $this->ip_address   = SBC::getIpAddress();
        $this->rd           = SBC::rd();

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

        // Open
        $db->open();

        // Admin Required + Process Data
        $User->setFrontpage();
        $User->admin($db);
        $User->requireAdminFlag('manage_forum_categories');
        $this->user_id = $User->getUserId();

        // Count Categories
        $this->countCategories($db);

        // Insert New Category
        $this->insertCategory($db);

        // Close
        $db->close();

        // Header
        header('Location: https://www.sketchbook.cafe/admin/forum_categories/');
        exit;
    }

    // Count Categories: Make sure we don't go over a set limit
    final private function countCategories(&$db)
    {
        $method = 'AdminForumCategoriesSubmit->countCategories()';

        // Initialize Vars
        $max_categories = 10;

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Count (no statement)
        $sql = 'SELECT COUNT(*)
            FROM forums
            WHERE iscategory=1
            AND isdeleted=0';
        $result = $db->sql_query($sql);
        $row    = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);

        // Total
        $total = isset($row[0]) ? (int) $row[0] : 0;
        if ($total < 1)
        {
            $total = 0;
        }

        // Check
        if ($total > $max_categories)
        {
            SBC::devError('max categories reached ('.$max_categories.')',$method);
        }
    }

    // Insert New Category
    final private function insertCategory(&$db)
    {
        $method = 'AdminForumCategoriesSubmit->insertCategory()';

        // Initialize Vars
        $time               = time();
        $ip_address         = $this->ip_address;
        $rd                 = $this->rd;
        $user_id            = $this->user_id;
        $name               = $this->category;
        $name_code          = $this->category_code;
        $description        = $this->description;
        $description_code   = $this->description_code;

        // Check just in case
        if ($user_id < 1)
        {
            SBC::devError('$user_id is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Insert New Forum Category
        $sql = 'INSERT INTO forums
            SET rd=?,
            user_id=?,
            ip_created=?,
            ip_updated=?,
            date_created=?,
            date_updated=?,
            name=?,
            name_code=?,
            description=?,
            description_code=?,
            iscategory=1,
            isdeleted=1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('iissiissss',$rd,$user_id,$ip_address,$ip_address,$time,$time,$name,$name_code,$description,$description_code);
        SBC::statementExecute($stmt,$db,$sql,$method);

        // Check if it was successful
        $sql = 'SELECT id
            FROM forums
            WHERE rd=?
            AND user_id=?
            AND date_created=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('iii',$rd,$user_id,$time);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Category ID
        $id = isset($row['id']) ? (int) $row['id'] : 0;
        if ($id < 1)
        {
            SBC::devError('could not insert new forum category into database',$method);
        }
        $this->id = $id;

        // Mark Category as Not Deleted
        $sql = 'UPDATE forums
            SET isdeleted=0
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }
}