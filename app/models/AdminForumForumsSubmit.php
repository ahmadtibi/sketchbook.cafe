<?php

class AdminForumForumsSubmit
{
    // Forum ID
    private $forum_id = 0;
    private $category_id = 0;

    private $user_id = 0;
    private $ip_address = '';
    private $rd = 0;

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
        sbc_function('rd');

        // Initialize Vars
        $this->ip_address   = $_SERVER['REMOTE_ADDR'];
        $this->rd           = rd();

        // Category ID
        $this->category_id  = isset($_POST['category_id']) ? (int) $_POST['category_id'] : 0;
        if ($this->category_id < 1)
        {
            error('Dev error: $category_id is not set for AdminForumForumsSubmit->construct()');
        }

        // Forum Name
        $ForumNameObject = new Message(array
        (
            'name'          => 'forumname',
            'min'           => 1,
            'column_max'    => 250,
            'nl2br'         => 0,
            'basic'         => 0,
            'ajax'          => 0,
            'images'        => 0,
            'videos'        => 0,
        ));
        $ForumNameObject->insert($_POST['forumname']);

        // Textarea Settings
        $TextareaSettings = new TextareaSettings('admin_forum_forum_description');
        $TextareaSettings->setValue('');
        $message_settings   = $TextareaSettings->getSettings();

        // Description
        $DescriptionObject  = new Message($message_settings);
        $DescriptionObject->insert($_POST['description']);

        // Set SQL Vars
        $this->name             = $ForumNameObject->getMessage();
        $this->name_code        = $ForumNameObject->getMessageCode();
        $this->description      = $DescriptionObject->getMessage();
        $this->description_code = $DescriptionObject->getMessageCode();

        // Open
        $db->open();

        // Admin Required + Process Data
        $User->setFrontpage();
        $User->admin($db);
        $User->requireAdminFlag('manage_forum_categories');
        $this->user_id = $User->getUserId();

        // Check if category exists
        $this->checkCategory($db);

        // Count Forums for Category
        $this->countForums($db);

        // Insert New Forum into Category
        $this->insertNewForum($db);

        // Create Forum Tables
        $this->createForumTables($db);

        // Update Forum
        $this->updateForum($db);

        // Close
        $db->close();

        // Header
        header('Location: https://www.sketchbook.cafe/admin/forum_forums/');
        exit;
    }

    // Check if category exists
    final private function checkCategory(&$db)
    {
        // Initialize Vars
        $category_id    = $this->category_id;

        // Check just in case
        if ($category_id < 1)
        {
            error('Dev error: $category_id is not set for AdminForumForumsSubmit->checkCategory()');
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Category
        $sql = 'SELECT id, iscategory, isdeleted
            FROM forums
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$category_id);
        if (!$stmt->execute())
        {
            error('Could not execute statement (get category) for AdminForumForumsSubmit->checkCategory()');
        }
        $result = $stmt->get_result();
        $row    = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        $stmt->close();


        // Check
        $category_id    = isset($row['id']) ? (int) $row['id'] : 0;
        if ($category_id < 1)
        {
            error('Could not find category in database');
        }

        // Make sure it's a category
        if ($row['iscategory'] != 1)
        {
            error('Odd.. this is not a category.');
        }

        // Make sure it's not deleted
        if ($row['isdeleted'] == 1)
        {
            error('Category no longer exists');
        }
    }

    // Count Forums
    final private function countForums(&$db)
    {
        // Initialize Vars
        $max_forums     = 10;
        $category_id    = $this->category_id;

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Count
        $sql = 'SELECT COUNT(*)
            FROM forums
            WHERE parent_id=?
            AND isforum=1
            AND isdeleted=0';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$category_id);
        if (!$stmt->execute())
        {
            error('Could not execute statement (count forums) for AdminForumForumsSubmit->countForums()');
        }
        $result = $stmt->get_result();
        $row    = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        $stmt->close();

        // Total
        $total  = isset($row[0]) ? (int) $row[0] : 0;

        // Check Total
        if ($total > $max_forums)
        {
            error('Maximum forums reached for Category');
        }
    }

    // Insert New Forum
    final private function insertNewForum(&$db)
    {
        // Initialize Vars
        $time               = time();
        $rd                 = $this->rd;
        $user_id            = $this->user_id;
        $ip_address         = $this->ip_address;
        $category_id        = $this->category_id;
        $name               = $this->name;
        $name_code          = $this->name_code;
        $description        = $this->description;
        $description_code   = $this->description_code;

        // Verify all information
        if ($user_id < 1 || $category_id < 1)
        {
            error('Dev error: $user_id:'.$user_id.', $category_id:'.$category_id.' for AdminForumForumsSubmit->insertNewForum()');
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Insert new forum
        $sql = 'INSERT INTO forums
            SET rd=?,
            user_id=?,
            parent_id=?,
            ip_created=?,
            ip_updated=?,
            date_created=?,
            date_updated=?,
            name=?,
            name_code=?,
            description=?,
            description_code=?,
            isforum=1,
            isdeleted=1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('iiissiissss',$rd,$user_id,$category_id,$ip_address,$ip_address,$time,$time,$name,$name_code,$description,$description_code);
        if (!$stmt->execute())
        {
            error('Could not execute statement (insert new forum) for AdminForumForumsSubmit->insertNewForum()');
        }
        $stmt->close();

        // Get Forum ID
        $sql = 'SELECT id
            FROM forums
            WHERE rd=?
            AND user_id=?
            AND date_created=?
            AND isforum=1
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('iii',$rd,$user_id,$time);
        if (!$stmt->execute())
        {
            error('Could not execute statement (get forum ID) for AdminForumForumsSubmit->insertNewForum()');
        }
        $result = $stmt->get_result();
        $row    = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        $stmt->close();

        // Forum ID?
        $forum_id   = isset($row['id']) ? (int) $row['id'] : 0;
        if ($forum_id < 1)
        {
            error('Dev error: Could not insert new forum for AdminForumForumsSubmit->insertNewForum()');
        }
        $this->forum_id = $forum_id;
    }

    // Create Forum Tables
    final private function createForumTables(&$db)
    {
        // Initialize Vars
        $forum_id   = $this->forum_id;
        if ($forum_id < 1)
        {
            error('Dev error: $forum_id is not set for AdminForumForumsSubmit->createForumTables()');
        }

        // Classes
        sbc_class('TableForum');

        // Forum Table
        $ForumTable = new TableForum($forum_id);
        $ForumTable->checkTables($db);
    }

    // Update Forum
    final private function updateForum(&$db)
    {
        // Initialize Vars
        $forum_id   = $this->forum_id;
        if ($forum_id < 1)
        {
            error('Dev error: $forum_id is not set for AdminForumForumsSubmit->updateForum()');
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Update forum and mark it as not deleted
        $sql = 'UPDATE forums
            SET isdeleted=0
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$forum_id);
        if (!$stmt->execute())
        {
            error('Could not execute statement (update forum) for AdminForumForumsSubmit->updateForum()');
        }
        $stmt->close();
    }
}