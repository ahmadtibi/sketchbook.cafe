<?php
// @author          Kameloh
// @lastUpdated     2016-05-16

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\Form\Form as Form;
use SketchbookCafe\TextareaSettings\TextareaSettings as TextareaSettings;
use SketchbookCafe\PageNumbers\PageNumbers as PageNumbers;

class ForumPage
{
    private $user_id = 0;
    public $view_time = [];

    public $Form = '';
    private $forum_id = 0;
    public $forum_row = [];
    public $forum_admin_result = '';
    public $forum_admin_rownum = 0;

    public $threads_result = '';
    public $threads_rownum = 0;

    private $obj_array = [];

    // Challenge Info
    private $challenge_row = [];

    // Page Numbers
    private $pageno = 0;
    private $ppage = 20;
    private $pages = 0;
    private $offset = 0;
    public $total = 0;
    public $pagenumbers = '';
    public $pages_min = 0;
    public $pages_max = 0;
    public $pages_total = 0;

    // Construct
    public function __construct()
    {
        $method = 'ForumPage->__construct()';
    }

    // Set Page Number
    final public function setPageNumber($pageno)
    {
        $method = 'ForumPage->setPageNumber()';

        // Set
        $this->pageno = isset($pageno) ? (int) $pageno : 0;
        if ($this->pageno < 1)
        {
            $this->pageno = 0;
        }
    }

    // Set Forum ID
    final public function setForumId($forum_id)
    {
        $method = 'ForumPage->setForumId()';

        $this->forum_id = isset($forum_id) ? (int) $forum_id : 0;
        if ($this->forum_id < 1)
        {
            SBC::devError('$forum_id is not set',$method);
        }
    }

    // Process
    final public function process(&$obj_array)
    {
        $method = 'ForumPage->process()';

        // Set
        $this->obj_array    = &$obj_array;

        // Initialize Objects
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // Initialize Vars
        $forum_id   = $this->forum_id;
        if ($forum_id < 1)
        {
            SBC::devError('$forum_id is not set',$method);
        }

        // Open Connection
        $db->open();

        // Admin Required + Process Data
        $User->setFrontpage();
        $User->optional($db);
        $this->user_id = $User->getUserId();

        // Get Forum Information
        $this->getForumInfo($db);

        // Page Numbers
        $ppage          = 20;
        $pageno         = $this->pageno;
        $total          = $this->total;
        $pages_link     = 'https://www.sketchbook.cafe/forum/'.$forum_id.'/{page_link}/';

        // Page Nmbers (SQL)
        $offset         = $pageno * $ppage;
        $this->offset   = $offset;
        $this->ppage    = $ppage;
        $this->pages    = ceil($total / $ppage);

        // Get Threads
        $this->getThreads($db);

        // Users Only
        if ($this->user_id > 0)
        {
            // Get Thread Timers
            $this->getThreadTimers($db);
        }

        // Get Forum Admins
        $this->getForumAdmins($db);

        // Process all data
        $ProcessAllData = new ProcessAllData();

        // Close Connection
        $db->close();

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

        // Form
        $Form   = new Form(array
        (
            'name'      => 'newforumthread',
            'action'    => 'https://www.sketchbook.cafe/forum/new_thread/',
            'method'    => 'POST',
        ));

        // Forum ID
        $Form->field['forum_id'] = $Form->hidden(array
        (
            'name'      => 'forum_id',
            'value'     => $forum_id,
        ));

        $i = 1;
        while ($i < 11)
        {
            // Poll
            $Form->field['poll'.$i] = $Form->input(array
            (
                'name'          => 'poll'.$i,
                'type'          => 'text',
                'max'           => 100,
                'value'         => '',
                'placeholder'   => 'poll answer '.$i,
                'css'           => 'fpInputPoll',
            ));
            $i++;
        }

        // Title
        $Form->field['name']     = $Form->input(array
        (
            'name'          => 'name',
            'type'          => 'text',
            'max'           => 100,
            'value'         => '',
            'placeholder'   => 'title',
            'css'           => 'input500 fpInputTitle',
        ));

        // Textarea Settings
        $TextareaSettings = new TextareaSettings('forum_thread');
        $Form->field['message'] = $Form->textarea($TextareaSettings->getSettings());

        // Set 
        $this->Form = $Form;
    }

    // Get Forum Information
    final private function getForumInfo(&$db)
    {
        $method = 'ForumPage->getForumInfo()';

        // Initialize Vars
        $forum_id   = $this->forum_id;
        if ($forum_id < 1)
        {
            SBC::devError('$forum_id is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Forum Information
        $sql = 'SELECT id, parent_id, name, description, total_threads, isforum, isdeleted
            FROM forums
            WHERE id=?
            LIMIT 1';
        $stmt       = $db->prepare($sql);
        $stmt->bind_param('i',$forum_id);
        $forum_row  = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Check
        $forum_id = isset($forum_row['id']) ? (int) $forum_row['id'] : 0;
        if ($forum_id < 1)
        {
            SBC::userError('Forum not found');
        }

        // Make sure it's a forum and not a category
        if ($forum_row['isforum'] != 1)
        {
            SBC::userError('Invalid forum');
        }

        // Check if deleted
        if ($forum_row['isdeleted'] == 1)
        {
            SBC::userError('Forum no longer exists');
        }

        // Get Parent Category
        $parent_id = $forum_row['parent_id'];
        if ($parent_id < 1)
        {
            SBC::devError('$parent_id is not set for Forum('.$forum_id.')',$method);
        }

        // Parent
        $sql = 'SELECT id, name
            FROM forums
            WHERE id=?
            LIMIT 1';
        $stmt           = $db->prepare($sql);
        $stmt->bind_param('i',$parent_id);
        $category_row   = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Set
        $this->total        = $forum_row['total_threads'];
        $this->forum_row    = $forum_row;
        $this->category_row = $category_row;
    }

    // Get Forum Threads
    final private function getThreads(&$db)
    {
        $method = 'ForumPage->getThreads()';

        // Initialize Objects and Vars
        $Member         = &$this->obj_array['Member'];
        $forum_id       = $this->forum_id;
        $offset         = $this->offset;
        $ppage          = $this->ppage;
        $pageno         = $this->pageno;
        $challenge_list = '';
        if ($pageno < 1)
        {
            $pageno = 0;
        }
        if ($forum_id < 1)
        {
            SBC::devError('$forum_id is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe_forums');

        // Table Name
        $table_name = 'forum'.$forum_id.'x';

        // Get Threads
        $sql = 'SELECT thread_id
            FROM '.$table_name.'
            ORDER BY date_bumped
            DESC
            LIMIT '.$offset.', '.$ppage;
        $result = $db->sql_query($sql);
        $rownum = $db->sql_numrows($result);

        // Did we find anything?
        $id_list = '';
        if ($rownum > 0)
        {
            // Loop
            while ($trow = mysqli_fetch_assoc($result))
            {
                // Do we have an ID?
                if ($trow['thread_id'] > 0)
                {
                    $id_list .= $trow['thread_id'].' ';
                }
            }
            mysqli_data_seek($result,0);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Clean List
        $id_list = str_replace(' ',',',trim($id_list));

        // ID List
        $threads_result = '';
        $threads_rownum = 0;
        if (!empty($id_list))
        {
            // Get Threads
            $sql = 'SELECT id, challenge_id, poll_id, user_id, date_created, date_updated, title, last_user_id,
                total_comments, total_views, total_users, is_poll, is_locked, is_sticky, isdeleted
                FROM forum_threads
                WHERE id IN('.$id_list.')
                ORDER BY date_bumped
                DESC';
            $threads_result = $db->sql_query($sql);
            $threads_rownum = $db->sql_numrows($threads_result);

            if ($threads_rownum > 0)
            {
                while ($trow = mysqli_fetch_assoc($threads_result))
                {
                    if ($trow['challenge_id'] > 0)
                    {
                        $challenge_list .= $trow['challenge_id'].' ';
                    }
                }
                mysqli_data_seek($threads_result,0);
            }

            // Add Members
            $Member->idAddRows($threads_result,'user_id');
            $Member->idAddRows($threads_result,'last_user_id');

            // Challenges?
            if (!empty($challenge_list))
            {
                $this->getChallengePending($db,$challenge_list);
            }
        }

        // Set
        $this->threads_result   = $threads_result;
        $this->threads_rownum   = $threads_rownum;
    }

    // Get Thread Timers
    private function getThreadTimers(&$db)
    {
        $method = 'ForumPage->getThreadTimers()';

        // Initialize Vars
        $user_id    = $this->user_id;
        if ($user_id < 1)
        {
            return null;
        }

        // Threads?
        $id_list = '';
        if ($this->threads_rownum > 0)
        {
            // Loop
            while ($trow = mysqli_fetch_assoc($this->threads_result))
            {
                if ($trow['id'] > 0)
                {
                    $this->view_time[$trow['id']]['date_updated']   = $trow['date_updated'];
                    $this->view_time[$trow['id']]['date_viewed']    = 0; // initialize
                    $id_list .= $trow['id'].' ';
                }
            }
            mysqli_data_seek($this->threads_result,0);
        }

        // Clean
        $id_list = str_replace(' ',',',trim($id_list));

        // Get IDs
        if (!empty($id_list))
        {
            // Switch
            $db->sql_switch('sketchbookcafe_users');

            // Table
            $table_name = 'u'.$user_id.'vt';

            // Get Threads
            $sql = 'SELECT cid, pda
                FROM '.$table_name.'
                WHERE cid IN('.$id_list.')';
            $vt_result  = $db->sql_query($sql);
            $vt_rownum  = $db->sql_numrows($vt_result);

            // Results?
            if ($vt_rownum > 0)
            {
                // Loop
                while ($trow = mysqli_fetch_assoc($vt_result))
                {
                    $this->view_time[$trow['cid']]['date_viewed'] = $trow['pda'];
                }
                mysqli_data_seek($vt_result,0);
            }
        }
    }

    // Get Forum Admins
    final private function getForumAdmins(&$db)
    {
        $method = 'ForumPage->getForumAdmins()';

        // Initialize
        $forum_id   = $this->forum_id;
        $Member     = &$this->obj_array['Member'];

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Forum Admins
        $sql = 'SELECT user_id
            FROM forum_admins
            WHERE forum_id=?';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$forum_id);
        if (!$stmt->execute())
        {
            SBC::devError('Could not get forum admins',$method);
        }
        $this->forum_admin_result = $stmt->get_result();
        $this->forum_admin_rownum = $db->sql_numrows($this->forum_admin_result);

        // Add Users
        $Member->idAddRows($this->forum_admin_result,'user_id');
    }

    // Get Challenge Pending Items
    final private function getChallengePending(&$db,$challenge_list)
    {
        $method = 'ForumMain->getChallengePending()';

        // Initialize
        $challenge_list = SBC::idClean($challenge_list);
        if (empty($challenge_list))
        {
            return null;
        }

        // Create array
        $temp_array = explode(',',$challenge_list);
        foreach ($temp_array as $value)
        {
            $this->challenge_row[$value]['id'] = $value;
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get challenge info
        $sql = 'SELECT id, total_pending
            FROM challenges
            WHERE id IN('.$challenge_list.')';
        $result = $db->sql_query($sql);
        $rownum = $db->sql_numrows($result);
        if ($rownum > 0)
        {
            while ($trow = mysqli_fetch_assoc($result))
            {
                $this->challenge_row[$trow['id']] = array
                (
                    'total_pending' => $trow['total_pending'],
                );
            }
            mysqli_data_seek($result,0);
        }
    }

    // Get Challenge Row
    final public function getChallengeRow()
    {
        return $this->challenge_row;
    }
}