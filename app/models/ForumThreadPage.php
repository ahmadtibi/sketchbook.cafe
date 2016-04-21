<?php

class ForumThreadPage
{
    private $thread_id = 0;
    private $user_id = 0;

    public $Form = '';
    public $thread_row = [];
    public $forum_row = [];
    public $category_row = [];

    public $comments_result = '';
    public $comments_rownum = 0;

    private $obj_array = [];

    // Total
    private $total = 0;

    // Page Numbers
    private $pageno = 0;
    private $ppage = 20; // 20 threads per page
    private $pages = 0;
    private $offset = 0;
    public $pagenumbers = '';
    public $pages_min = 0;
    public $pages_max = 0;
    public $pages_total = 0;

    // Construct
    public function __construct()
    {

    }

    // Set Thread ID
    final public function setThreadId($thread_id)
    {
        $this->thread_id = isset($thread_id) ? (int) $thread_id : 0;
        if ($this->thread_id < 1)
        {
            error('Dev error: $thread_id is not set for ForumThreadPage->setThreadId()');
        }
    }

    // Set Page Number
    final public function setPageNumber($number)
    {
        $this->pageno = isset($number) ? (int) $number : 0;
        if ($this->pageno < 1)
        {
            $this->pageno = 0;
        }
    }

    // Process
    final public function process(&$obj_array)
    {
        // Object Array
        $this->obj_array    = &$obj_array;

        // Initialize Objects
        $db                 = &$obj_array['db'];
        $User               = &$obj_array['User'];
        $Member             = &$obj_array['Member'];
        $Comment            = &$obj_array['Comment'];

        // Classes
        sbc_class('Form');
        sbc_class('TextareaSettings');
        sbc_class('PageNumbers');

        // Thread Id
        $thread_id = $this->thread_id;
        if ($thread_id < 1)
        {
            error('Dev error: $thread_id is not set for ForumThreadPage->process()');
        }

        // Open Connection
        $db->open();

        // Admin Required + Process Data
        $User->setFrontpage();
        $User->optional($db);
        $user_id = $User->getUserId();
        $this->user_id = $user_id;

        // Get Thread Info
        $this->getThreadInfo($db,$Member,$Comment);

        // Page Numbers
        $ppage          = 20;
        $pageno         = $this->pageno;
        $total          = $this->total;
        $pages_link     = 'https://www.sketchbook.cafe/forum/thread/22/{page_link}/';

        // Page Numbers (SQL)
        $offset         = $pageno * $ppage;
        $this->offset   = $offset;
        $this->ppage    = $ppage;
        $this->pages    = ceil($total / $ppage);

        // Get Thread Comments
        $this->getComments($db);

        // Process all data
        $ProcessAllData = new ProcessAllData();

        // Close Connection
        $db->close();

        // Page Numbers
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

        // New Form
        $Form = new Form(array
        (
            'name'      => 'forumthreadreplyform',
            'action'    => 'https://www.sketchbook.cafe/forum/thread_reply/',
            'method'    => 'POST',
        ));

        // Thread ID
        $Form->field['thread_id'] = $Form->hidden(array
        (
            'name'      => 'thread_id',
            'value'     => $thread_id,
        ));

        // Textarea Settings
        $TextareaSettings = new TextareaSettings('forum_reply');
        $TextareaSettings->setValue('');
        $message_settings   = $TextareaSettings->getSettings();

        // Textarea
        $Form->field['message'] = $Form->textarea($message_settings);

        // Set Vars
        $this->Form = $Form;
    }

    // Get Thread Information
    final private function getThreadInfo(&$db,&$Member,&$Comment)
    {
        // Initialize Vars
        $thread_id = $this->thread_id;
        if ($thread_id < 1)
        {
            error('Dev error: $thread_id is not set for ForumThreadPage->getThreadInfo()');
        }

        // Switch
        $db->sql_switch('sketchbookcafe');
 
        // Get Thread Information
        $sql = 'SELECT id, forum_id, user_id, date_created, comment_id, title, total_comments, isdeleted
            FROM forum_threads
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$thread_id);
        if (!$stmt->execute())
        {
            error('Could not execute statement (get thread info) for ForumThreadPage->getThreadInfo()');
        }
        $result     = $stmt->get_result();
        $thread_row = $db->sql_fetchrow($result);

        // Check
        $thread_id  = isset($thread_row['id']) ? (int) $thread_row['id'] : 0;
        if ($thread_id < 1)
        {
            error('Could not find thread in database');
        }

        // Not deleted
        if ($thread_row['isdeleted'] == 1)
        {
            error('Thread no longer exists');
        }

        // Forum ID
        $forum_id   = $thread_row['forum_id'];
        if ($forum_id < 1)
        {
            error('Thread does not have a forum set.');
        }

        // Set Total for Page Numbers
        $this->total = $thread_row['total_comments'];

        // Get Forum Information
        $sql = 'SELECT id, parent_id, name
            FROM forums
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$forum_id);
        if (!$stmt->execute())
        {
            error('Could not execute statement (get forum information) for ForumThreadPage->getThreadInfo()');
        }
        $result     = $stmt->get_result();
        $forum_row  = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        $stmt->close();

        // Forum ID
        $forum_id    = isset($forum_row['id']) ? (int) $forum_row['id'] : 0;
        if ($forum_id < 1)
        {
            error('Forum thread is not part of a forum forum and it cannot be viewed');
        }

        // Category ID
        $category_id    = isset($forum_row['parent_id']) ? (int )$forum_row['parent_id'] : 0;
        if ($category_id < 1)
        {
            error('Forum thread is not part of a forum category and it cannot be viewed');
        }

        // Get Category
        $sql = 'SELECT id, name
            FROM forums
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$category_id);
        if (!$stmt->execute())
        {
            error('Could not execute statement (get category) for ForumThreadPage->getThreadInfo()');
        }
        $result         = $stmt->get_result();
        $category_row   = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        $stmt->close();

        // Category ID
        $category_id    = isset($category_row['id']) ? (int) $category_row['id'] : 0;
        if ($category_row < 1)
        {
            error('Forum thread is not part of a forum category and it cannot be viewed');
        }

        // Set
        $this->category_row = $category_row;
        $this->forum_row    = $forum_row;
        $this->thread_row   = $thread_row;

        // Add Member's User ID
        $Member->idAddOne($thread_row['user_id']);

        // Add Comment ID
        $Comment->idAddOne($thread_row['comment_id']);
   }

    // Get Thread Comments
    final private function getComments(&$db)
    {
        // Initialize Objects and Vars
        $Comment    = &$this->obj_array['Comment'];
        $Member     = &$this->obj_array['Member'];
        $thread_id  = $this->thread_id;
        $offset     = $this->offset;
        $pageno     = $this->pageno;
        $ppage      = $this->ppage;

        // Pagenumbers
        if ($pageno < 1)
        {
            $pageno = 0;
        }

        // Check
        if ($thread_id < 1)
        {
            error('Dev error: $thread_id is not set for ForumThreadPage->getComments()');
        }

        // Switch
        $db->sql_switch('sketchbookcafe_forums');

        // Table
        $table_name = 't'.$thread_id.'d';

        // Get Comments
        $sql = 'SELECT cid
            FROM '.$table_name.'
            ORDER BY id
            ASC
            LIMIT '.$offset.', '.$ppage;
        $comments_result = $db->sql_query($sql);
        $comments_rownum = $db->sql_numrows($comments_result);

        // Set Vars
        $this->comments_result  = $comments_result;
        $this->comments_rownum  = $comments_rownum;

        // Add Comment IDs
        $Comment->idAddRows($comments_result,'cid');
    }
}