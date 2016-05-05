<?php
// @author          Kameloh
// @lastUpdated     2016-04-27

use SketchbookCafe\SBC\SBC as SBC;

class AdminManageForumPage
{
    public $Form = '';
    public $categories_result = '';
    public $categories_rownum = 0;
    public $forums_result = '';
    public $forums_rownum = 0;
    public $f_admin_result = '';
    public $f_admin_rownum = 0;

    // Construct
    public function __construct(&$obj_array)
    {
        $method = 'AdminManageForumPage->__construct()';

        // Initialize Objects
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];
        $Member = &$obj_array['Member'];

        // Open Connection
        $db->open();

        // Admin Required + Process Data
        $User->setFrontpage();
        $User->admin($db);
        $User->requireAdminFlag('manage_forum');

        // Get Categories
        $this->getCategories($db);

        // Get Forums
        $this->getForums($db);

        // Get Administrators
        $this->getAdmins($db,$Member);

        // Process All User Data Last!
        $ProcessAllData = new ProcessAllData();

        // Close Connection
        $db->close();
    }

    // Get Categories
    final private function getCategories(&$db)
    {
        $method = 'AdminManageForumPage->getCategories()';

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Categories
        $sql = 'SELECT id, name, description
            FROM forums
            WHERE iscategory=1
            AND isdeleted=0
            ORDER BY forum_order
            ASC';
        $this->categories_result  = $db->sql_query($sql);
        $this->categories_rownum  = $db->sql_numrows($this->categories_result);
    }

    // Get Forums
    final private function getForums(&$db)
    {
        $method = 'AdminManageForumPage->getForums()';

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Forums
        $sql = 'SELECT id, parent_id, date_updated, name, description, total_threads, total_posts
            FROM forums
            WHERE isforum=1
            AND isdeleted=0
            ORDER BY forum_order
            ASC';
        $this->forums_result    = $db->sql_query($sql);
        $this->forums_rownum    = $db->sql_numrows($this->forums_result);
    }

    // Get Forum Admins
    final private function getAdmins(&$db,&$Member)
    {
        $method = 'AdminManageForumPage->getAdmins()';

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get admins
        $sql = 'SELECT id, user_id, forum_id
            FROM forum_admins';
        $this->f_admin_result   = $db->sql_query($sql);
        $this->f_admin_rownum   = $db->sql_numrows($this->f_admin_result);

        // Add to Members
        $Member->idAddRows($this->f_admin_result,'user_id');
    }
}