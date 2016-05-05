<?php
// @author          Kameloh
// @lastUpdated     2016-05-03

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\Form\Form as Form;

class AdminChallengeCategoriesPage
{
    public $Form = '';
    public $categories_result;
    public $categories_rownum = 0;

    // Construct
    public function __construct(&$obj_array)
    {
        $method = 'AdminChallengeCategoriesPage->__construct()';

        // Initialize
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // Open Connection
        $db->open();

        // Admin Required
        $User->setFrontpage();
        $User->admin($db);
        $User->requireAdminFlag('challenge_categories');

        // Get Categories
        $this->getCategories($db);

        // Process All Data
        $ProcessAllData = new ProcessAllData();

        // Close Connection
        $db->close();

        // New Form
        $Form   = new Form(array
        (
            'name'      => 'createcategoryform',
            'action'    => 'https://www.sketchbook.cafe/admin/create_challenge_category/',
            'method'    => 'POST',
        ));

        // Category
        $Form->field['category'] = $Form->input(array
        (
            'name'      => 'category',
            'type'      => 'text',
            'min'       => 1,
            'max'       => 250,
            'nl2br'     => 0,
            'basic'     => 0,
            'ajax'      => 0,
            'images'    => 0,
            'videos'    => 0,
        ));

        // Submit
        $Form->field['submit'] = $Form->submit(array
        (
            'name'      => 'Submit',
            'css'       => '',
        ));

        // Set
        $this->Form = $Form;
    }

    // Get Challenge Categories
    final private function getCategories(&$db)
    {
        $method = 'AdminChallengeCategoriesPage->getCategories()';

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Categories
        $sql = 'SELECT id, name
            FROM challenge_categories
            WHERE isdeleted=0
            ORDER BY name
            ASC';
        $this->categories_result = $db->sql_query($sql);
        $this->categories_rownum = $db->sql_numrows($this->categories_result);
    }
}