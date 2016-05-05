<?php
// @author          Kameloh
// @lastUpdated     2016-05-02

use SketchbookCafe\SBC\SBC as SBC;

class ForumSubscriptionsPage
{
    private $user_id = 0;
    private $sub_table = 0;
    private $thread_list = '';
    public $sub_result = '';
    public $sub_rownum = 0;
    public $threads_result = '';
    public $threads_rownum = 0;

    // Construct
    public function __construct(&$obj_array)
    {
        $method = 'ForumSubscriptionPage->__construct()';

        // Initialize
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // Open Connection
        $db->open();

        // User Required, Frontpage
        $User->setFrontpage();
        $User->required($db);
        $this->user_id = $User->getUserId();
        $this->sub_table = $User->getColumn('table_forum_subscriptions');

        // Get Subscriptions
        $this->getSubscriptions($db);

        // Get Threads
        $this->getForumThreads($db);

        // Process Data
        $ProcessAllData = new ProcessAllData();

        // Close Connection
        $db->close();
    }

    // Get Subscriptions
    final private function getSubscriptions(&$db)
    {
        $method = 'ForumSubscriptionPage->getSubscriptions()';

        // Initialize
        $user_id    = SBC::checkNumber($this->user_id,'$this->user_id');
        $sub_table  = $this->sub_table;

        // Any table?
        if ($sub_table < 1)
        {
            return null;
        }

        // Switch
        $db->sql_switch('sketchbookcafe_users');

        // Table
        $table_name = 'u'.$user_id.'fs';

        // Get Subs (non statement)
        $sql = 'SELECT tid, pda, lda
            FROM '.$table_name;
        $this->sub_result = $db->sql_query($sql);
        $this->sub_rownum = $db->sql_numrows($this->sub_result);

        // Create Threads List
        if ($this->sub_rownum > 0)
        {
            // Loop
            while ($trow = mysqli_fetch_assoc($this->sub_result))
            {
                if ($trow['tid'] > 0)
                {
                    $this->thread_list .= $trow['tid'] .' ';
                }
            }
            mysqli_data_seek($this->sub_result,0);
        }

        // Clean
        $this->thread_list = str_replace(' ',',',trim($this->thread_list));
    }

    // Get Forum Threads
    final private function getForumThreads(&$db)
    {
        $method = 'ForumSubscriptionPage->getForumThreads()';

        // Initialize
        $thread_list    = $this->thread_list;
        if (empty($thread_list))
        {
            return null;
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Threads (non statement)
        $sql = 'SELECT id, date_updated, title, isdeleted
            FROM forum_threads
            WHERE id IN('.$thread_list.')
            ORDER BY date_updated
            DESC';
        $this->threads_result   = $db->sql_query($sql);
        $this->threads_rownum   = $db->sql_numrows($this->threads_result);
    }
}