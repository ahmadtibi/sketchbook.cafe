<?php
// @author          Kameloh
// @lastUpdated     2016-05-03

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\OnlineOrganizer\OnlineOrganizer as OnlineOrganizer;
use SketchbookCafe\OnlineList\OnlineList as OnlineList;

class ForumMain
{
    public $categories_result = '';
    public $categories_rownum = 0;
    public $forums_result = '';
    public $forums_rownum = '';
    public $thread = [];
    private $user_id = 0;

    public $online_result;
    public $online_rownum = 0;

    private $obj_array = [];

    // Construct
    public function __construct(&$obj_array)
    {
        $method = 'ForumMain->__construct()';

        // Initialize Objects
        $this->obj_array    = &$obj_array;
        $db                 = &$obj_array['db'];
        $User               = &$obj_array['User'];

        // Open Connection
        $db->open();

        // Optional User
        $User->setFrontpage();
        $User->optional($db);
        $this->user_id = $User->getUserId();

        // Get Categories
        $this->getAll($db);

        // Process Threads
        $this->getThreads($db);

        // Online Organizer
        $OnlineOrganizer = new OnlineOrganizer($db);
        $OnlineOrganizer->updateUser($this->user_id);
        $OnlineOrganizer->clean();

        // Get Online List
        $OnlineList = new OnlineList($obj_array);
        $OnlineList->process();
        $this->online_result    = $OnlineList->getResult();
        $this->online_rownum    = $OnlineList->getRownum();

        // Process Data
        $ProcessAllData = new ProcessAllData();

        // Close Connection
        $db->close();
    }

    // Get Forum Categories and Forums
    final private function getAll(&$db)
    {
        $method = 'ForumMain->getAll()';

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
        $sql = 'SELECT id, parent_id, name, description, total_threads, total_posts, last_thread_id 
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

    // Get Threads
    final private function getThreads(&$db)
    {
        $method = 'ForumMain->getThreads()';

        $Member     = &$this->obj_array['Member'];

        $thread_ids = '';
        if ($this->forums_rownum > 0)
        {
            // Loop
            while ($trow = mysqli_fetch_assoc($this->forums_result))
            {
                if ($trow['last_thread_id'] > 0)
                {
                    $thread_ids .= $trow['last_thread_id'].' ';
                }
            }
            mysqli_data_seek($this->forums_result,0);
        }

        // Clean
        $thread_ids = str_replace(' ',',',trim($thread_ids));

        // If not empty
        if (!empty($thread_ids))
        {
            // Switch
            $db->sql_switch('sketchbookcafe');

            // Get Threads
            $sql = 'SELECT id, last_user_id, date_updated, title, total_views 
                FROM forum_threads
                WHERE id IN('.$thread_ids.')';
            $threads_result = $db->sql_query($sql);
            $threads_rownum = $db->sql_numrows($threads_result);

            // Did we find any threads?
            if ($threads_rownum > 0)
            {
                // Add User ID to the members class
                $Member->idAddRows($threads_result,'last_user_id');

                // Loop
                while ($trow = mysqli_fetch_assoc($threads_result))
                {
                    $id                                 = $trow['id'];
                    $this->thread[$id]['id']            = $id;
                    $this->thread[$id]['title']         = $trow['title'];
                    $this->thread[$id]['last_user_id']  = $trow['last_user_id'];
                    $this->thread[$id]['date_updated']  = $trow['date_updated'];
                }
                mysqli_data_seek($threads_result,0);
            }
        }
    }
}