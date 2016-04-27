<?php
// @author          Jonathan Maltezo (Kameloh)
// @lastUpdated     2016-04-27

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\Form\Form as Form;

class AdminFixForumTablePage
{
    public $Form = '';
    private $categories_result = '';
    private $categories_rownum = 0;
    private $forums_result = '';
    private $forums_rownum = 0;

    // Construct
    public function __construct(&$obj_array)
    {
        $method = 'AdminFixForumTablePage->__construct()';

        // Initialize Objects
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // Open Connection
        $db->open();

        // Admin Required + Process Data
        $User->setFrontpage();
        $User->admin($db);
        $User->requireAdminFlag('fix_forum_table');

        // Get Categories and Forums
        $this->getCategoriesAndForums($db);

        // Process All
        $ProcessAllData = new ProcessAllData();

        // Close Connection
        $db->close();

        // Create List Array
        $list = [];
        while ($crow = mysqli_fetch_assoc($this->categories_result))
        {
            // Add Category to List
            $temp_name          = 'Category: '.$crow['name'];
            $list[$temp_name]   = $crow['id'];

            // Forums
            while ($frow = mysqli_fetch_assoc($this->forums_result))
            {
                // Parent?
                if ($frow['parent_id'] == $crow['id'])
                {
                    // Add to list
                    $temp_name          = 'Forum: '.$frow['name'];
                    $list[$temp_name]   = $frow['id'];
                }
            }
            mysqli_data_seek($this->forums_result,0);

            // Add Spacer
            $temp_name          = '&nbsp;';
            $list[$temp_name]   = -1;
        }
        mysqli_data_seek($this->categories_result,0);

        // New Form
        $Form   = new Form(array
        (
            'name'      => 'fixforumform',
            'action'    => 'https://www.sketchbook.cafe/admin/fix_forum_table_submit/',
            'method'    => 'POST',
        ));

        // Forum List
        $list_input = array
        (
            'name'  => 'forum_id',
        );
        $Form->field['forum_id'] = $Form->dropdown($list_input,$list,0);

        // Submit
        $Form->field['submit']  = $Form->submit(array
        (
            'name'          => 'Submit',
            'css'           => '',
        ));

        // Set
        $this->Form = $Form;
    }

    // Get Categories and Forums
    final private function getCategoriesAndForums(&$db)
    {
        $method = 'AdminFixForumTablePage->getCategoriesAndForums()';

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Categories
        $sql = 'SELECT id, name
            FROM forums
            WHERE iscategory=1
            AND isdeleted=0
            ORDER BY forum_order
            ASC';
        $this->categories_result = $db->sql_query($sql);
        $this->categories_rownum = $db->sql_numrows($this->categories_result);

        // Get Forums
        $sql = 'SELECT id, parent_id, name
            FROM forums
            WHERE isforum=1
            AND isdeleted=0
            ORDER BY forum_order
            ASC';
        $this->forums_result    = $db->sql_query($sql);
        $this->forums_rownum    = $db->sql_numrows($this->forums_result);
    }
}