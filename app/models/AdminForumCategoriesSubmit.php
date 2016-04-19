<?php

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
        // Initialize Objects
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // Classes and Functions
        sbc_class('Message');
        sbc_class('TextareaSettings');
        sbc_function('rd');

        // Initialize Vars
        $this->ip_address   = $_SERVER['REMOTE_ADDR'];
        $this->rd           = rd();

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
            error('Dev error: max categories reached ('.$max_categories.') for AdminForumCategoriesSubmit->countCategories()');
        }
    }

    // Insert New Category
    final private function insertCategory(&$db)
    {
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
            error('Dev error: $user_id is not set for AdminForumCategories->insertCategory()');
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
        if (!$stmt->execute())
        {
            error('Could not execute statement (insert new forum category) for AdminForumCategoriesSubmit->insertCategory()');
        }
        $stmt->close();

        // Check if it was successful
        $sql = 'SELECT id
            FROM forums
            WHERE rd=?
            AND user_id=?
            AND date_created=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('iii',$rd,$user_id,$time);
        if (!$stmt->execute())
        {
            error('Could not execute statement (get forum category) for AdminForumCategoriesSubmit->insertCategory()');
        }
        $result = $stmt->get_result();
        $row    = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        $stmt->close();

        // Category ID
        $id = isset($row['id']) ? (int) $row['id'] : 0;
        if ($id < 1)
        {
            error('Dev error: could not insert new forum category into database for AdminForumCategoriesSubmit->insertCategory()');
        }
        $this->id = $id;

        // Mark Category as Not Deleted
        $sql = 'UPDATE forums
            SET isdeleted=0
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$id);
        if (!$stmt->execute())
        {
            error('Could not execute statement (update category) for AdminForumCategoriesSubmit->insertCategory()');
        }
        $stmt->close();
    }
}