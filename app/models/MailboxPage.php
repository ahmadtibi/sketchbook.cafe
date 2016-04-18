<?php
// Mailbox Page
class MailboxPage
{
    public $user_id = 0;
    public $isnew = [];

    // SQL
    public $result = '';
    public $rownum = 0;

    // Page Numbers
    public $total = 0;
    private $pageno = 0;
    private $ppage  = 10; // 10 Threads Per Page
    private $offset = 0;
    private $pages = 0;
    public $pagenumbers = '';
    public $pages_min = 0;
    public $pages_max = 0;
    public $pages_total = 0;

    // Construct
    public function __construct()
    {

    }

    // Set Page Number
    final public function setPageNumber($pageno)
    {
        $pageno = isset($pageno) ? (int) $pageno : 0;
        if ($pageno < 1)
        {
            $pageno = 0;
        }
        $this->pageno = $pageno;
    }

    // Process Page
    final public function processPage(&$obj_array)
    {
        // Initialize Objects
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];
        $Member = &$obj_array['Member'];

        // Classes
        sbc_class('PageNumbers');

        // Open Connection
        $db->open();

        // User Required
        $User->setFrontpage();
        $User->required($db);
        $user_id        = $User->getUserId();
        $this->user_id  = $user_id;

        // Force the user to have a mailbox update
        $User->forceMailboxUpdate($db);

        // Count Total Mail
        $this->countMail($db);

        // Page Numbers
        $ppage      = 1;
        $pageno     = $this->pageno;
        $total      = $this->total;
        $pages_link = 'https://www.sketchbook.cafe/mailbox/{pages_link}/';

        // Page Numbers for SQL
        $offset         = $pageno * $ppage;
        $this->offset   = $offset;
        $this->ppage    = $ppage;
        $this->pages    = ceil($total / $ppage);

        // Get User's Mailbox
        $this->getMail($db,$Member);

        // =====================================

        // Process All Data Last
        $ProcessAllData = new ProcessAllData();

        // Close Connection
        $db->close();

        echo '<div>pageno:'.$pageno.', total:'.$total.', ppage:'.$ppage.', link:'.$pages_link.'</div>';

        // Page numbers
        $PageNumbersObject  = new PageNumbers(array
        (
            'name'          => 'pagenumbers',
            'first'         => 0, // current page
            'current'       => $pageno, // page number
            'posts'         => $total, // count(*) value
            'ppage'         => $ppage, // max number of posts per page
            'display'       => 4, // numbers of pages to display as links per side
            'link'          => $pages_link, 
            'css_overlay'   => '',
            'css_inactive'  => 'pageNumbersItem pageNumbersItemUnselected',
            'css_active'    => 'pageNumbersItem pageNumbersItemSelected',
        ));
        $this->pagenumbers  = $PageNumbersObject->getPageNumbers();

        // More Pagenumbers
        $this->pages_min    = $PageNumbersObject->pages_min;
        $this->pages_max    = $PageNumbersObject->pages_max;
        $this->pages_total  = $PageNumbersObject->pages_total;

        echo $this->pagenumbers;
    }

    // Count Mail
    final private function countMail(&$db)
    {
        // Initialize Vars
        $mail_total_all = 0;
        $user_id        = $this->user_id;

        // Check
        if ($user_id < 1)
        {
            error('Dev error: $user_id is not set for MailboxPage->countMail()');
        }

        // Switch
        $db->sql_switch('sketchbookcafe_users');

        // Set Table
        $table_name = 'u'.$user_id.'m';

        // Count
        $sql = 'SELECT COUNT(*)
            FROM '.$table_name;
        $result = $db->sql_query($sql);
        $row    = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);

        // Total
        $total  = isset($row[0]) ? (int) $row[0] : 0;
        if ($total < 1)
        {
            $total = 0;
        }
        $this->total = $total;
    }

    // Get User's Mailbox
    final private function getMail(&$db,&$Member)
    {
        // Initialize Objects and Vars
        $Member     = &$Member;
        $user_id    = $this->user_id;
        $offset     = $this->offset;
        $ppage      = $this->ppage;
        $pageno     = $this->pageno;
        if ($pageno < 1)
        {
            $pageno = 0;
        }

        // Just in case
        if ($user_id < 1)
        {
            error('Dev error: $user_id is not set for MailboxPage->getMail()');
        }



        // Switch
        $db->sql_switch('sketchbookcafe_users');

        // Tables
        $table_name = 'u'.$user_id.'m';

        // Get Mail - not using a statement here since there isn't any user input?
        $sql = 'SELECT cid, isnew 
            FROM '.$table_name.'
            ORDER BY lastupdate
            DESC
            LIMIT '.$offset.', '.$ppage;
        $result = $db->sql_query($sql);
        $rownum = $db->sql_numrows($result);

        // Any results?
        $id_list    = '';
        if ($rownum > 0)
        {
            // Loop
            while ($trow = mysqli_fetch_assoc($result))
            {
                // Temp ID
                $temp_id    = $trow['cid'];
                if ($temp_id > 0)
                {
                    $id_list .= $temp_id.' ';

                    // Is New Array
                    $this->isnew[$temp_id] = $trow['isnew'];
                }
            }
            mysqli_data_seek($result,0);
        }

        // Reset vars
        unset($result);
        $rownum = 0;

        // Quick Clean
        $id_list = str_replace(' ',',',trim($id_list));

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Mail Threads
        if (!empty($id_list))
        {
            // Again, no statement :x.. might fix this later
            $sql = 'SELECT id, user_id, r_user_id, date_updated, title,
                    last_user_id, total_replies, isremoved, isdeleted
                FROM mailbox_threads
                WHERE id IN('.$id_list.') 
                ORDER BY date_updated
                DESC';
            $result = $db->sql_query($sql);
            $rownum = $db->sql_numrows($result);

            // Add Members by Result
            $Member->idAddRows($result,'user_id');
            $Member->idAddRows($result,'r_user_id');

            // Set
            $this->result   = $result;
            $this->rownum   = $rownum;
        }
    }
}