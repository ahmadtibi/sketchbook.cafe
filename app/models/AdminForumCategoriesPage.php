<?php
// @author          Jonathan Maltezo (Kameloh)
// @lastUpdated     2016-04-27

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\Form\Form as Form;
use SketchbookCafe\TextareaSettings\TextareaSettings as TextareaSettings;

class AdminForumCategoriesPage
{
    public $Form = '';
    public $result = '';
    public $rownum = '';

    // Construct
    public function __construct(&$obj_array)
    {
        $method = 'AdminForumCategoriesPage->__construct()';

        // Initialize Objects
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // Open Connection
        $db->open();

        // Admin Required + Process Data
        $User->setFrontpage();
        $User->admin($db);
        $User->requireAdminFlag('manage_forum_categories');

        // Get Forum Stuff
        $this->getForumInfo($db);

        // Process All User Data Last!
        $ProcessAllData = new ProcessAllData();

        // Close Connection
        $db->close();

        // New Form
        $Form   = new Form(array
        (
            'name'      => 'forumcategoriesform',
            'action'    => 'https://www.sketchbook.cafe/admin/forum_categories_submit/',
            'method'    => 'POST',
        ));

        // Category Name
        $Form->field['category'] = $Form->input(array
        (
            'name'          => 'category',
            'type'          => 'text',
            'max'           => 50,
            'value'         => '',
            'placeholder'   => 'category name',
            'css'           => 'input300',
        ));

        // Textarea Settings
        $TextareaSettings = new TextareaSettings('admin_forum_category_description');
        $TextareaSettings->setValue('');
        $message_settings   = $TextareaSettings->getSettings();

        // Category Description
        $Form->field['description'] = $Form->textarea($message_settings);

        // Set
        $this->Form = $Form;
    }

    // Get Forum Info
    final private function getForumInfo(&$db)
    {
        $method = 'AdminForumCategoriesPage->getForumInfo()';

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Categories and Forums
        $sql = 'SELECT id, parent_id, name, description, iscategory, isforum, isdeleted
            FROM forums
            ORDER BY forum_order
            ASC';
        $result = $db->sql_query($sql);
        $rownum = $db->sql_numrows($result);

        // Set
        $this->result = $result;
        $this->rownum = $rownum;
    }
}