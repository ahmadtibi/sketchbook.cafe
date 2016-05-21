<?php
// @author          Kameloh
// @lastUpdated     2016-05-21
namespace SketchbookCafe\Forums;

use SketchbookCafe\SBC\SBC as SBC;

class Forums
{
    private $categories_result = [];
    private $categories_rownum = 0;
    private $forums_result = [];
    private $forums_rownum = 0;
    private $threads_result = [];
    private $threads_rownum = 0;
    private $thread = [];

    private $data = [];

    private $thread_list = '';

    private $obj_array = [];

    public function __construct(&$obj_array)
    {
        $this->obj_array = &$obj_array;
    }

    // Get Forum Categories and Forums
    final public function findAll()
    {
        $method = 'Forums->getAll()';

        $this->findCategories();
        $this->findForums();
        $this->findThreads();
    }

    // Find Categories
    final public function findCategories()
    {
        $method = 'Forums->findCategories()';

        // Initialize
        $db = &$this->obj_array['db'];

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Categories
        $sql = 'SELECT id, name, description
            FROM forums
            WHERE iscategory=1
            AND isdeleted=0
            ORDER BY forum_order
            ASC';
        $this->categories_result = $db->sql_query($sql);
        $this->categories_rownum = $db->sql_numrows($this->categories_result);
    }

    // Find Forums
    final public function findForums($category_id = 0)
    {
        $method = 'Forums->findForums()';

        // Initialize
        $db             = &$this->obj_array['db'];
        $category_id    = isset($category_id) ? (int) $category_id : 0;
        $sql_and        = '';

        // Specific forum?
        if ($category_id > 0)
        {
            $sql_and    = ' AND parent_id='.$category_id;
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Forums
        $sql = 'SELECT id, parent_id, name, description, total_threads, total_posts, last_thread_id
            FROM forums
            WHERE isforum=1 '.$sql_and.'
            AND isdeleted=0
            ORDER BY forum_order
            ASC';
        $this->forums_result = $db->sql_query($sql);
        $this->forums_rownum = $db->sql_numrows($this->forums_result);

        // Loop
        while ($trow = mysqli_fetch_assoc($this->forums_result))
        {
            if ($trow['last_thread_id'] > 0)
            {
                $this->thread_list .= $trow['last_thread_id'].' ';
            }
        }
        mysqli_data_seek($this->forums_result,0);
    }

    // Find Threads
    final public function findThreads()
    {
        $method = 'Forums->findThreads()';

        // Initialize
        $db             = &$this->obj_array['db'];
        $Member         = &$this->obj_array['Member'];
        $thread_list    = $this->thread_list;

        // Clean
        $thread_list = SBC::idClean($thread_list);

        if (!empty($thread_list))
        {
            // Switch
            $db->sql_switch('sketchbookcafe');

            // Get Threads
            $sql = 'SELECT id, last_user_id, date_updated, title, total_views
                FROM forum_threads
                WHERE id IN('.$thread_list.')';
            $this->threads_result = $db->sql_query($sql);
            $this->threads_rownum = $db->sql_numrows($this->threads_result);

            // Any results?
            if ($this->threads_rownum > 0)
            {
                $Member->idAddRows($this->threads_result,'last_user_id');

                while ($trow = mysqli_fetch_assoc($this->threads_result))
                {
                    $this->thread[$trow['id']] = $trow;
                }
                mysqli_data_seek($this->threads_result,0);
            }
        }
    }

    // Get Data
    final public function getData()
    {
        $array = array
        (
            'categories_result' => &$this->categories_result,
            'forums_result'     => &$this->forums_result,
            'threads_result'    => &$this->threads_result,
            'thread'            => &$this->thread,
        );

        return $array;
    }
}