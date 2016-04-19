<?php

class ForumMain
{
    public $categories_result = '';
    public $categories_rownum = 0;
    public $forums_result = '';
    public $forums_rownum = '';

    // Construct
    public function __construct(&$obj_array)
    {
        // Initialize Objects
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // Open Connection
        $db->open();

        // Optional User
        $User->setFrontpage();
        $User->optional($db);

        // Get Categories
        $this->getAll($db);

        // Process Data
        $ProcessAllData = new ProcessAllData();

        // Close Connection
        $db->close();
    }

    // Get Forum Categories and Forums
    final private function getAll(&$db)
    {
        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Categories
        $sql = 'SELECT id, name, description
            FROM forums
            WHERE iscategory=1
            AND isdeleted=0
            ORDER BY forum_order
            ASC';
        $result = $db->sql_query($sql);
        $rownum = $db->sql_numrows($result);

        // Set
        $this->categories_result = $result;
        $this->categories_rownum = $rownum;

        // Unset
        unset($result);
        unset($rownum);

        // Get Forums
        $sql = 'SELECT id, parent_id, name, description
            FROM forums
            WHERE isforum=1
            AND isdeleted=0
            ORDER BY forum_order
            ASC';
        $result = $db->sql_query($sql);
        $rownum = $db->sql_numrows($result);

        // Set
        $this->forums_result = $result;
        $this->forums_rownum = $rownum;
    }
}