<?php

class AdminForumForumsPage
{
    public $Form = '';
    public $forums_result = '';
    public $forums_rownum = 0;
    public $categories_result = '';
    public $categories_rownum = 0;

    // Construct
    public function __construct(&$obj_array)
    {
        // Initialize Objects
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // Classes
        sbc_class('Form');
        sbc_class('TextareaSettings');

        // Open Connection
        $db->open();

        // Admin Required + Process Data
        $User->setFrontpage();
        $User->admin($db);
        $User->requireAdminFlag('manage_forum_forums');

        // Get Categories
        $this->getCategories($db);

        // Get Forums
        $this->getForums($db);

        // Process All User Data Last!
        $ProcessAllData = new ProcessAllData();

        // Close Connection
        $db->close();

        // Set vars
        $categories_result = $this->categories_result;
        $categories_rownum = $this->categories_rownum;

        // New Form
        $Form   = new Form(array
        (
            'name'      => 'forumsforumform',
            'action'    => 'https://www.sketchbook.cafe/admin/forum_forums_submit/',
            'method'    => 'POST',
        ));

        // Dropdown: Category
        $list = '';
        while ($trow = mysqli_fetch_assoc($categories_result))
        {
            $temp_name          = $trow['name'];
            $list[$temp_name]   = $trow['id'];
        }
        mysqli_data_seek($categories_result,0);

        $input  = array
        (
            'name'  => 'category_id',
        );
        $current_value              = 0;
        $Form->field['categories']  = $Form->dropdown($input,$list,$current_value);

        // Forum Name
        $Form->field['forumname'] = $Form->input(array
        (
            'name'          => 'forumname',
            'type'          => 'text',
            'max'           => 50,
            'value'         => '',
            'placeholder'   => 'forum name',
            'css'           => 'input300',
        ));

        // Textarea Settings
        $TextareaSettings = new TextareaSettings('admin_forum_forum_description');
        $TextareaSettings->setValue('');
        $message_settings   = $TextareaSettings->getSettings();

        // Forum Description
        $Form->field['description'] = $Form->textarea($message_settings);

        // Set
        $this->Form = $Form;
    }

    // Get Categories
    private function getCategories(&$db)
    {
        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Categories
        $sql = 'SELECT id, name, description
            FROM forums
            WHERE iscategory=1
            AND isdeleted=0
            ORDER BY forum_order
            DESC';
        $result = $db->sql_query($sql);
        $rownum = $db->sql_numrows($result);

        // Set
        $this->categories_result = $result;
        $this->categories_rownum = $rownum;
    }

    // Get Forums
    private function getForums(&$db)
    {
        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Forums
        $sql = 'SELECT id, name, description, parent_id
            FROM forums
            WHERE isforum=1
            AND isdeleted=0
            ORDER BY forum_order
            DESC';
        $result = $db->sql_query($sql);
        $rownum = $db->sql_numrows($result);

        // Set
        $this->forums_result = $result;
        $this->forums_rownum = $rownum;
    }
}