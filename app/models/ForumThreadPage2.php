<?php
// @author          Kameloh
// @lastUpdated     2016-05-06

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\Form\Form as Form;
use SketchbookCafe\TextareaSettings\TextareaSettings as TextareaSettings;
use SketchbookCafe\PageNumbers\PageNumbers as PageNumbers;
use SketchbookCafe\UserOrganizer\UserOrganizer as UserOrganizer;
use SketchbookCafe\ThreadOrganizer\ThreadOrganizer as ThreadOrganizer;
use SketchbookCafe\ChallengeOrganizer\ChallengeOrganizer as ChallengeOrganizer;
use SketchbookCafe\Challenge\Challenge as Challenge;

class ForumThreadPage
{
    private $challenge_id = 0;
    private $thread_id = 0;
    private $user_id = 0;
    private $forum_id = 0;
    private $poll_id = 0;

    public $challenge_row;

    public $ChallengeForm = '';
    public $SubscribeForm = '';
    public $PollForm = '';
    public $poll_row = '';
    public $ForumAdmin = '';
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

    // Subscription
    private $time = 0;
    private $thread_date_updated = 0;
    private $subscription_lda = 0; // subscription last updated for user table
    private $table_forum_subscriptions = 0;
    public $subscribed = 0;

    // Challenges
    public $entry = [];
    public $challenge_entries_result = [];
    public $challenge_entries_rownum = [];

    // Construct
    public function __construct()
    {
        $method = 'ForumThreadPage->__construct()';
        $this->time = SBC::getTime();
    }

    // Set Thread ID
    final public function setThreadId($thread_id)
    {
        $method = 'ForumThreadPage->setThreadId()';

        $this->thread_id = isset($thread_id) ? (int) $thread_id : 0;
        if ($this->thread_id < 1)
        {
            SBC::devError('$thread_id is not set',$method);
        }
    }

    // Set Page Number
    final public function setPageNumber($number)
    {
        $method = 'ForumThreadPage->setPageNumber()';

        $this->pageno = isset($number) ? (int) $number : 0;
        if ($this->pageno < 1)
        {
            $this->pageno = 0;
        }
    }

    // Process
    final public function process(&$obj_array)
    {
        $method = 'ForumThreadPage->process()';

        // Initialize
        $this->obj_array    = &$obj_array;
        $db                 = &$obj_array['db'];
        $User               = &$obj_array['User'];
        $Member             = &$obj_array['Member'];
        $Comment            = &$obj_array['Comment'];

        // Check Thread
        if ($this->thread_id < 1)
        {
            SBC::devError('$thread_id is not set',$method);
        }

        // Open Connection
        $db->open();

        // User Optional
        $User->setFrontpage();
        $User->optional($db);
        $this->user_id = $User->getUserId();
        $this->table_forum_subscriptions = $User->getColumn('table_forum_subscriptions');

        // Verify Thread (sets thread_id, forum_id, challenge_id)
        $this->getThreadInfo($db);

        // Challenge Info
        if ($this->challenge_id > 0)
        {
            $this->getChallengeInfo($db);
        }

        // Get Poll Info
        if ($this->poll_id > 0)
        {
            $this->getPollInfo($db);
        }

        // Is the user a forum admin?
        if ($User->isAdmin())
        {
            $User->getForumAdminFlags($db,$this->forum_id);
        }

        // Page Numbers
        $ppage          = 10;
        $pageno         = $this->pageno;
        $total          = $this->total;
        $pages_link     = 'https://www.sketchbook.cafe/forum/thread/'.$this->thread_id.'/{page_link}/';
        $this->offset   = $pageno * $ppage;
        $this->ppage    = $ppage;
        $this->pages    = ceil($total / $ppage);

        // Get Thread Comments
        $this->getComments($db);

        // Forum Organizer: Viewed Thread
        if ($this->user_id > 0)
        {
            $UserOrganizer = new UserOrganizer($db);
            $UserOrganizer->viewedThread($this->thread_id,$this->user_id);
        }

        // Check if the user has subscribed to this thread
        if ($this->user_id > 0 && $this->table_forum_subscriptions > 0)
        {
            $this->checkSubscribed($db);
        }

        // Thread View Count
        $ThreadOrganizer = new ThreadOrganizer($db);
        $ThreadOrganizer->viewCountUpdate($this->thread_id,$this->user_id);

        // Process all data
        $ProcessAllData = new ProcessAllData();

        // Challenge Entries (after processing)
        if ($this->challenge_id > 0)
        {
            $this->processEntries($db);
        }

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

        // Create Subscriber Form
        if ($this->user_id > 0)
        {
            $this->createSubscribeForm();
        }

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
            'value'     => $this->thread_id,
        ));

        // Textarea Settings
        $TextareaSettings = new TextareaSettings('forum_reply');

        // Textarea
        $Form->field['message'] = $Form->textarea($TextareaSettings->getSettings());

        // New Form
        $PollForm = new Form(array
        (
            'name'      => 'forumpollvote',
            'action'    => 'https://www.sketchbook.cafe/forum/poll_vote/',
            'method'    => 'POST',
            'inactive'  => 'Vote',
            'active'    => 'Voting...',
        ));

        // Hidden
        $PollForm->field['poll_id'] = $PollForm->hidden(array
        (
            'name'      => 'poll_id',
            'value'     => $this->poll_id,
        ));

        // Submit
        $PollForm->field['submit'] = $PollForm->submit(array
        (
            'name'      => 'Submit',
            'css'       => 'pollvotebutton',
        ));

        // Create Challenge Form
        if ($this->user_id > 0 && $this->challenge_id > 0)
        {
            $this->createChallengeForm();
        }

        // Set Vars
        $this->PollForm         = $PollForm;
        $this->Form             = $Form;
    }

    // Get Thread Information
    final private function getThreadInfo(&$db)
    {
        $method = 'ForumThreadPage->getThreadInfo()';

        // Initialize
        $Member     = &$this->obj_array['Member'];
        $Comment    = &$this->obj_array['Comment'];
        $thread_id  = $this->thread_id;
        if ($thread_id < 1)
        {
            SBC::devError('$thread_id is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');
 
        // Get Thread Information
        $sql = 'SELECT id, challenge_id, poll_id, forum_id, user_id, date_created, date_updated, comment_id, title, total_comments, 
            is_poll, is_locked, is_sticky, isdeleted
            FROM forum_threads
            WHERE id=?
            LIMIT 1';
        $stmt       = $db->prepare($sql);
        $stmt->bind_param('i',$thread_id);
        $thread_row = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Check
        $thread_id  = isset($thread_row['id']) ? (int) $thread_row['id'] : 0;
        if ($thread_id < 1)
        {
            SBC::userError('Could not find thread in database');
        }

        // Not deleted
        if ($thread_row['isdeleted'] == 1)
        {
            SBC::userError('Thread no longer exists');
        }

        // Set date for subscriptions
        $this->thread_date_updated = $thread_row['date_updated'];

        // Forum ID
        $forum_id   = $thread_row['forum_id'];
        if ($forum_id < 1)
        {
            SBC::userError('Thread does not have a forum set.');
        }
        $this->forum_id = $forum_id;

        // Set Total for Page Numbers
        $this->total = $thread_row['total_comments'];

        // Get Forum Information
        $sql = 'SELECT id, parent_id, name
            FROM forums
            WHERE id=?
            LIMIT 1';
        $stmt       = $db->prepare($sql);
        $stmt->bind_param('i',$forum_id);
        $forum_row  = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Forum ID
        $forum_id    = isset($forum_row['id']) ? (int) $forum_row['id'] : 0;
        if ($forum_id < 1)
        {
            SBC::userError('Forum thread is not part of a forum forum and it cannot be viewed');
        }

        // Category ID
        $category_id    = isset($forum_row['parent_id']) ? (int )$forum_row['parent_id'] : 0;
        if ($category_id < 1)
        {
            SBC::userError('Forum thread is not part of a forum category and it cannot be viewed');
        }

        // Get Category
        $sql = 'SELECT id, name
            FROM forums
            WHERE id=?
            LIMIT 1';
        $stmt           = $db->prepare($sql);
        $stmt->bind_param('i',$category_id);
        $category_row   = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Category ID
        $category_id    = isset($category_row['id']) ? (int) $category_row['id'] : 0;
        if ($category_row < 1)
        {
            SBC::userError('Forum thread is not part of a forum category and it cannot be viewed');
        }

        // Set
        $this->challenge_id = $thread_row['challenge_id'];
        $this->poll_id      = $thread_row['poll_id'];
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
        $method = 'ForumThreadPage->getComments()';

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
            SBC::devError('$thread_id is not set',$method);
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

    // Get Poll Info
    final private function getPollInfo(&$db)
    {
        $method = 'ForumThreadPage->getPollInfo()';

        // Initialize
        $poll_id    = $this->poll_id;

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Poll Info
        $sql = 'SELECT id, message1, message2, message3, message4, message5, 
            message6, message7, message8, message9, message10, 
            vote1, vote2, vote3, vote4, vote5, 
            vote6, vote7, vote8, vote9, vote10,
            total_votes, is_locked, is_hidden, isdeleted
            FROM forum_polls
            WHERE id=?
            LIMIT 1';
        $stmt       = $db->prepare($sql);
        $stmt->bind_param('i',$poll_id);
        $poll_row   = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Verify
        $poll_id    = isset($poll_row['id']) ? (int) $poll_row['id'] : 0;
        if ($poll_id < 1)
        {
            SBC::devError('Cannot find Poll in database',$method);
        }

        // Set
        $this->poll_row = $poll_row;
    }

    // Check Subscription
    final private function checkSubscribed(&$db)
    {
        $method = 'ForumThreadPage->checkSubscribed()';

        // Initialize
        $time                   = $this->time;
        $thread_date_updated    = $this->thread_date_updated;
        $thread_id              = SBC::checkNumber($this->thread_id,'$this->thread_id');
        $user_id                = $this->user_id;
        $table                  = $this->table_forum_subscriptions;

        // Switch
        $db->sql_switch('sketchbookcafe_users');

        // Table
        $table_name = 'u'.$user_id.'fs';

        // Check
        $sql = 'SELECT id, lda
            FROM '.$table_name.'
            WHERE tid=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$thread_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Subscribed?
        $id = isset($row['id']) ? (int) $row['id'] : 0;
        if ($id > 0)
        {
            $this->subscribed = 1;
            $this->subscription_lda = $row['lda'];

            // Update subscription's last update column
            $sql = 'UPDATE '.$table_name.'
                SET lda=?,
                pda=?
                WHERE id=?
                LIMIT 1';
            $stmt = $db->prepare($sql);
            $stmt->bind_param('iii',$thread_date_updated,$time,$id);
            SBC::statementExecute($stmt,$db,$sql,$method);
        }
    }

    // Get Challenge Info
    final private function getChallengeInfo(&$db)
    {
        $method = 'ForumThreadPage->getChallengeInfo()';

        // Initialize
        $challenge_id   = $this->challenge_id;

        // Challenge
        $Challenge = new Challenge($db);
        $Challenge->setChallengeId($challenge_id);
        $Challenge->process();
        // $Challenge->getPending();

        // Set Arrays
        $this->challenge_row            = $Challenge->challenge_row;
        // $this->challenge_entries_result = $Challenge->challenge_entries_result;
        // $this->challenge_entries_rownum = $Challenge->challenge_entries_rownum;

        // print_r($this->challenge_entries_result['pending']);

    }

    // Create Challenge Form
    final private function createChallengeForm()
    {
        $method = 'ForumThreadPage->createChallengeForm()';

        // New Form
        $ChallengeForm  = new Form(array
        (
            'name'      => 'challengeform',
            'action'    => 'https://www.sketchbook.cafe/challenge/entry_submit/',
            'method'    => 'POST',
        ));

        // Challenge Id
        $ChallengeForm->field['challenge_id'] = $ChallengeForm->hidden(array
        (
            'name'      => 'challenge_id',
            'value'     => $this->challenge_id,
        ));

        // File Input
        $ChallengeForm->field['imagefile'] = $ChallengeForm->file(array
        (
            'name'  => 'imagefile',
        ));

        // Normal Submit
        $ChallengeForm->field['submit'] = $ChallengeForm->submit(array
        (
            'name'  => 'Submit',
            'css'   => '',
        ));

        // Upload Submit
        $ChallengeForm->field['upload'] = $ChallengeForm->upload(array
        (
            'name'      => 'imagefile',
            'imagefile' => 'imagefile',
            'post_url'  => 'https://www.sketchbook.cafe/challenge/entry_submit/',
            'css'       => '',
        ));

        // Textarea
        $TS = new TextareaSettings('challenge_reply');
        $ChallengeForm->field['message'] = $ChallengeForm->textarea($TS->getSettings());

        // Set
        $this->ChallengeForm = $ChallengeForm;
    }

    // Process Challenge Entries
    final private function processEntries(&$db)
    {
        $method = 'ForumThreadPage->processEntries()';

        // Initialize
        $Comment    = &$this->obj_array['Comment'];

        // Challenge Organizer
        $ChallengeOrganizer = new ChallengeOrganizer($db);
        $ChallengeOrganizer->idAddEntriesByResult($Comment->result,'entry_id');

        // Entry
        $this->entry = $ChallengeOrganizer->getPending($db);
    }

    // Create Subscribe Form
    final private function createSubscribeForm()
    {
        $method = 'ForumThreadPage->createSubscribeForm()';

        // Subscribe Name
        $subscribe_name_inactive    = 'Subscribe to Thread';
        $subscribe_name_active      = 'Subscribing...';
        if ($this->subscribed == 1)
        {
            $subscribe_name_inactive    = 'Unsubscribe from Thread';
            $subscribe_name_active      = 'Unsubscribing...';
        }

        // If user, create Subscribe Form
        $SubscribeForm  = new Form(array
        (
            'name'      => 'subscribeform',
            'action'    => 'https://www.sketchbook.cafe/forum/thread_subscribe/',
            'method'    => 'POST',
            'inactive'  => $subscribe_name_inactive,
            'active'    => $subscribe_name_active,
        ));

        // Thread ID
        $SubscribeForm->field['thread_id'] = $SubscribeForm->hidden(array
        (
            'name'      => 'thread_id',
            'value'     => $this->thread_id,
        ));

        // Submit
        $SubscribeForm->field['submit'] = $SubscribeForm->submit(array
        (
            'name'      => 'Submit',
            'css'       => '',
        ));

        // Set
        $this->SubscribeForm = $SubscribeForm;
    }
}