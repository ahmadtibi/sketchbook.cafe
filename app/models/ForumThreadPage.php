<?php
// @author          Kameloh
// @lastUpdated     2016-05-16

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\Form\Form as Form;
use SketchbookCafe\TextareaSettings\TextareaSettings as TextareaSettings;
use SketchbookCafe\ForumThread\ForumThread as ForumThread;
use SketchbookCafe\ChallengeOrganizer\ChallengeOrganizer as ChallengeOrganizer;
use SketchbookCafe\PageNumbers\PageNumbers as PageNumbers;
use SketchbookCafe\ThreadOrganizer\ThreadOrganizer as ThreadOrganizer;
use SketchbookCafe\UserOrganizer\UserOrganizer as UserOrganizer;

class ForumThreadPage
{
    // Thread Info
    private $forum_id = 0;
    private $thread_id = 0;
    private $poll_id = 0;
    private $challenge_id = 0;
    private $total_comments = 0;

    // User Info
    private $user_id = 0;
    private $subscribed = 0;
    private $user_entry_id = 0;

    // Comments
    public $comments_result;
    public $comments_rownum = 0;

    // Arrays
    public $thread_row;
    public $forum_row;
    public $category_row;
    public $challenge_row = [];
    public $entry; // if post is pending... might remove this
    public $entries_result = [];
    public $entries_rownum = 0;

    // Forms
    public $Form;
    public $SubscribeForm;
    public $PollForm;
    public $ChallengeForm;

    // Page Numbers
    public $PageNumbers = [];
    private $pageno = 0;
    private $ppage = 10;

    // Objects
    private $obj_array;

    // Construct
    public function __construct(&$obj_array)
    {
        $this->obj_array = &$obj_array;
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
    final public function setPageNumber($pageno)
    {
        $method = 'ForumThreadPage->setPageNumber()';

        $this->pageno = isset($pageno) ? (int) $pageno : 0;
    }

    // Process Page
    final public function process()
    {
        $method = 'ForumThreadPage->process()';

        // Check Thread
        if ($this->thread_id < 1)
        {
            SBC::devError('$thread_id is not set',$method);
        }

        // Initialize
        $db         = &$this->obj_array['db'];
        $User       = &$this->obj_array['User'];
        $Member     = &$this->obj_array['Member'];
        $Comment    = &$this->obj_array['Comment'];

        // Open Connection
        $db->open();

        // User Optional
        $User->setFrontpage();
        $User->optional($db);
        $this->user_id = $User->getUserId();

        // Forum Thread
        $ForumThread = new ForumThread($this->obj_array);
        $ForumThread->setThreadId($this->thread_id);
        $ForumThread->setUserId($this->user_id);
        $ForumThread->setSubscriptionTable($User->getColumn('table_forum_subscriptions'));
        $ForumThread->setPageNumber($this->pageno);
        $ForumThread->process();
        $this->forum_id     = $ForumThread->getForumId();
        $this->challenge_id = $ForumThread->challenge_id;

        // Forum Admin?
        if ($User->isAdmin())
        {
            $User->getForumAdminFlags($db,$this->forum_id);
        }

        // Get Total Comments
        $this->total_comments = $ForumThread->getTotalComments();

        // User Organizer
        if ($this->user_id > 0)
        {
            $UserOrganizer = new UserOrganizer($db);
            $UserOrganizer->viewedThread($this->thread_id,$this->user_id);
        }

        // Thread Organizer
        $ThreadOrganizer = new ThreadOrganizer($db);
        $ThreadOrganizer->viewCountUpdate($this->thread_id,$this->user_id);

        // Challenge Gallery
        if ($this->challenge_id > 0)
        {
            $this->getChallengeGallery($db);
        }

        // Process all data
        $ProcessAllData = new ProcessAllData();

        // Challenge Entries (after processing)
        if ($this->challenge_id > 0)
        {
            if ($this->user_id > 0)
            {
                // Check if they've already submitted an entry
                $this->checkSubmittedEntry($db);
            }
            $this->processChallengeEntries($db);
        }

        // Close Connection
        $db->close();

        // Set Data
        $this->poll_id          = $ForumThread->poll_id;
        $this->challenge_id     = $ForumThread->challenge_id;
        $this->comments_result  = $ForumThread->comments_result;
        $this->comments_rownum  = $ForumThread->comments_rownum;
        $this->subscribed       = $ForumThread->subscribed;
        $this->thread_row       = $ForumThread->thread_row;
        $this->forum_row        = $ForumThread->forum_row;
        $this->category_row     = $ForumThread->category_row;
        $this->poll_row         = $ForumThread->poll_row;
        $this->challenge_row    = $ForumThread->challenge_row;

        // Create Forms
        $this->createReplyForm();
        $this->createSubscriptionForm();
        $this->createPollForm();
        // $this->createChallengeForm();

        // Page Numbers
        $this->createPageNumbers();
    }

    // Create Form
    final private function createReplyForm()
    {
        // New Form
        $Form = new Form(array
        (
            'name'      => 'forumthreadreplyform',
            'action'    => 'https://www.sketchbook.cafe/forum/thread_reply/',
            'method'    => 'POST',
        ));

        // Thread
        $Form->field['thread_id'] = $Form->hidden(array
        (
            'name'      => 'thread_id',
            'value'     => $this->thread_id,
        ));

        // Textarea
        $TS = new TextareaSettings('forum_reply');
        $Form->field['message'] = $Form->textarea($TS->getSettings());

        // Challenge
        if ($this->challenge_id > 0)
        {
            // Challenge Id
            $Form->field['challenge_id'] = $Form->hidden(array
            (
                'name'      => 'challenge_id',
                'value'     => $this->challenge_id,
            ));

            // File Input
            $Form->field['imagefile'] = $Form->file(array
            (
                'name'  => 'imagefile',
            ));
        }

        // Set Form
        $this->Form = $Form;
    }

    // Create Subscription Form
    final private function createSubscriptionForm()
    {
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
            'css'       => 'cursor thread_subscribe_submit',
        ));

        // Set
        $this->SubscribeForm = $SubscribeForm;
    }

    // Create Poll Form
    final private function createPollForm()
    {
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

        // Set
        $this->PollForm = $PollForm;
    }

    // Create Challenge Form
    final private function createChallengeForm()
    {
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
    final private function processChallengeEntries(&$db)
    {
        $method = 'ForumThreadPage->processChallengeEntries()';

        // Initialize
        $Comment    = &$this->obj_array['Comment'];

        // Challenge Organizer
        $ChallengeOrganizer = new ChallengeOrganizer($db);
        $ChallengeOrganizer->idAddEntriesByResult($Comment->result,'entry_id');

        // Entry
        $this->entry = $ChallengeOrganizer->getPending($db);
    }

    // Create Page Numbers
    final private function createPageNumbers()
    {
        // Create Page Numbers
        $PageNumbersObj = new PageNumbers(array
        (
            'name'          => 'pagenumbers',
            'first'         => 0, // current page
            'current'       => $this->pageno, // page number
            'posts'         => $this->total_comments, // count(*) value
            'ppage'         => $this->ppage, // max number of posts per page
            'display'       => 4, // numbers of pages to display as links per side
            'link'          => 'https://www.sketchbook.cafe/forum/thread/'.$this->thread_id.'/{page_link}/', 
            'css_overlay'   => '',
            'css_inactive'  => 'pageNumbersItem pageNumbersItemUnselected',
            'css_active'    => 'pageNumbersItem pageNumbersItemSelected',
        ));
        $this->PageNumbers = array
        (
            'pagenumbers'   => $PageNumbersObj->getPageNumbers(),
            'pages_min'     => $PageNumbersObj->pages_min,
            'pages_max'     => $PageNumbersObj->pages_max,
            'pages_total'   => $PageNumbersObj->pages_total,
        );
    }

    // Check Submitted Entry
    final public function checkSubmittedEntry(&$db)
    {
        $method = 'ForumThreadPage->checkSubmittedEntry()';

        // Initialize
        $challenge_id   = SBC::checkNumber($this->challenge_id,'$this->challenge_id');
        $user_id        = SBC::checkNumber($this->user_id,'$this->user_id');
        $table_name     = 'fc'.$challenge_id.'l';

        // Switch
        $db->sql_switch('sketchbookcafe_challenges');

        // Check if they already have an entry
        $sql = 'SELECT entry_id
            FROM '.$table_name.'
            WHERE uid=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$user_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // User Entry ID?
        $this->user_entry_id    = isset($row['entry_id']) ? (int) $row['entry_id'] : 0;
        if ($this->user_entry_id < 1)
        {
            $this->user_entry_id = 0;
        }
    }

    // Get Entry ID for User
    final public function getUserEntryId()
    {
        return $this->user_entry_id;
    }

    // Get Challenge Gallery
    final private function getChallengeGallery(&$db)
    {
        $method = 'ForumThreadPage->getChallengeGallery()';

        // Initialize
        $Member         = $this->obj_array['Member'];
        $Images         = $this->obj_array['Images'];
        $challenge_id   = $this->challenge_id;
        $table_name     = 'fc'.$challenge_id.'l';
        $entry_list     = '';
        if ($challenge_id < 1)
        {
            return null;
        }

        // Switch
        $db->sql_switch('sketchbookcafe_challenges');

        // Get Entries
        $sql = 'SELECT entry_id
            FROM '.$table_name.'
            WHERE ispending=0
            ORDER BY id
            DESC
            LIMIT 20';
        $result = $db->sql_query($sql);
        $rownum = $db->sql_numrows($result);

        // Entries?
        if ($rownum > 0)
        {
            // Loop
            while ($trow = mysqli_fetch_assoc($result))
            {
                // ID?
                if ($trow['entry_id'] > 0)
                {
                    $entry_list .= $trow['entry_id'].' ';
                }
            }
            mysqli_data_seek($result,0);
        }

        // Clean
        $entry_list = SBC::idClean($entry_list);

        // Empty?
        if (empty($entry_list))
        {
            return null;
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Entries
        $sql = 'SELECT id, image_id, user_id, date_created, isnew, ispending
            FROM challenge_entries
            WHERE id IN('.$entry_list.')
            ORDER BY id
            DESC';
        $this->entries_result = $db->sql_query($sql);
        $this->entries_rownum = $db->sql_numrows($this->entries_result);

        // Add Arrays
        if ($this->entries_rownum > 0)
        {
            $Member->idAddRows($this->entries_result,'user_id');
            $Images->idAddRows($this->entries_result,'image_id');
        }
    }
}