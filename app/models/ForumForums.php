<?php
// @author          Jonathan Maltezo (Kameloh)
// @lastUpdated     2016-04-27
// $db must be open

use SketchbookCafe\SBC\SBC as SBC;

class ForumForums
{
    public $result = '';
    public $rownum = 0;

    // Construct
    public function __construct(&$obj_array)
    {
        // Initialize Objects
        $db = &$obj_array['db'];

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Forums
        $sql = 'SELECT id, parent_id, date_updated, name, description, forum_order, total_threads, total_posts, last_user_id, last_thread_id
            FROM forums
            WHERE isforum=1
            AND isdeleted=0
            ORDER BY forum_order
            ASC';
        $this->result   = $db->sql_query($sql);
        $this->rownum   = $db->sql_numrows($this->result);
    }
}