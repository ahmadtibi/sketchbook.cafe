<?php
// @author          Kameloh
// @lastUpdated     2016-05-16
use SketchbookCafe\SBC\SBC as SBC;

class Forum extends Controller
{
    protected $obj_array = '';

    public function __construct(&$obj_array)
    {
        $this->obj_array = &$obj_array;
    }

    // Subscriptions Page
    public function subscriptions()
    {
        $method = 'forum->subscriptions()';

        // Model
        $SubPageObj     = $this->model('ForumSubscriptionsPage',$this->obj_array);
        $threads_result = $SubPageObj->threads_result;
        $threads_rownum = $SubPageObj->threads_rownum;
        $sub_result     = $SubPageObj->sub_result;
        $sub_rownum     = $SubPageObj->sub_rownum;

        // View
        $this->view('sketchbookcafe/header');
        $this->view('forum/subscriptions',
        [
            'threads_result'    => $threads_result,
            'threads_rownum'    => $threads_rownum,
            'sub_result'        => $sub_result,
            'sub_rownum'        => $sub_rownum,
        ]);
        $this->view('sketchbookcafe/footer');
    }

    // Subscribe to Thread
    public function thread_subscribe()
    {
        $method = 'forum->thread_subscribe()';

        // Model
        $this->model('ThreadSubscribe',$this->obj_array);
    }

    // Poll Vote
    public function poll_vote()
    {
        $method = 'forum->poll_vote()';

        // Model
        $this->model('PollVote',$this->obj_array);
    }

    // Lock Post
    public function lock_post($comment_id = 0)
    {
        $method = 'forum->lock_post()';

        // Initialize
        $comment_id = isset($comment_id) ? (int) $comment_id : 0;
        if ($comment_id < 1)
        {
            SBC::devError('Comment ID is not set',$method);
        }

        // Model
        $CommentObj = $this->model('CommentPostLock',$this->obj_array);
        $CommentObj->setCommentId($comment_id);
        $CommentObj->process();
    }

    // Sticky Thread
    public function thread_sticky($comment_id = 0)
    {
        $method = 'forum->thread_sticky()';

        // Initialize
        $comment_id = isset($comment_id) ? (int) $comment_id : 0;
        if ($comment_id < 1)
        {
            SBC::devError('Comment ID is not set',$method);
        }

        // Model
        $ThreadObj  = $this->model('ForumThreadSticky',$this->obj_array);
        $ThreadObj->setCommentId($comment_id);
        $ThreadObj->process();
    }

    // Thread Bump
    public function thread_bump($comment_id = 0)
    {
        $method = 'forum->thread_bump()';

        // Initialize
        $comment_id = isset($comment_id) ? (int) $comment_id : 0;
        if ($comment_id < 1)
        {
            SBC::devError('Comment ID is not set',$method);
        }

        // Model
        $ThreadObj   = $this->model('ForumThreadBump',$this->obj_array);
        $ThreadObj->setCommentId($comment_id);
        $ThreadObj->process();
    }

    // Thread Lock
    public function thread_lock($comment_id = 0)
    {
        $method = 'forum->thread_lock()';

        // Initialize
        $comment_id = isset($comment_id) ? (int) $comment_id : 0;
        if ($comment_id < 1)
        {
            SBC::devError('Comment ID is not set',$method);
        }

        // Model
        $ThreadObj   = $this->model('ForumThreadLock',$this->obj_array);
        $ThreadObj->setCommentId($comment_id);
        $ThreadObj->process();
    }

    // Forum Thread
    public function thread($thread_id = 0, $pageno = 0)
    {
        $method = 'forum->thread()';

        // Check
        if ($thread_id < 1)
        {
            SBC::userError('Thread ID not set');
        }

        // Model
        $PageObj = $this->model('ForumThreadPage',$this->obj_array);
        $PageObj->setThreadId($thread_id);
        $PageObj->setPageNumber($pageno);
        $PageObj->process();

        // Vars
        $user_entry_id = $PageObj->getUserEntryId();

        // View
        $this->view('sketchbookcafe/header');
        $this->view('forum/threadpage',
        [
            'User'              => &$this->obj_array['User'],
            'Comment'           => &$this->obj_array['Comment'],
            'Member'            => &$this->obj_array['Member'],
            'Form'              => &$PageObj->Form,
            'SubscribeForm'     => &$PageObj->SubscribeForm,
            'PollForm'          => &$PageObj->PollForm,
            'thread_row'        => &$PageObj->thread_row,
            'forum_row'         => &$PageObj->forum_row,
            'category_row'      => &$PageObj->category_row,
            'poll_row'          => &$PageObj->poll_row,
            'challenge_row'     => &$PageObj->challenge_row,
            'entry'             => &$PageObj->entry,
            'comments_result'   => &$PageObj->comments_result,
            'comments_rownum'   => &$PageObj->comments_rownum,
            'PageNumbers'       => &$PageObj->PageNumbers,
            'user_entry_id'     => $user_entry_id,
            'entries_result'    => &$PageObj->entries_result,
            'entries_rownum'    => &$PageObj->entries_rownum,
        ]);
        $this->view('sketchbookcafe/footer');
    }
    public function thread_reply()
    {
        // Model
        $this->model('ForumThreadReply',$this->obj_array);
    }

    // Submit New Thread
    public function new_thread()
    {
        // Model
        $this->model('ForumNewThread',$this->obj_array);
    }

    // Main Page
    public function index($forum_id = 0, $pageno = 0)
    {
        // Objects
        $User       = $this->obj_array['User'];

        $forum_id = isset($forum_id) ? (int) $forum_id : 0;

        // If forum id is not set then goto the main forum page
        if ($forum_id < 1)
        {
            // Model
            $MainPageObject     = $this->model('ForumMain',$this->obj_array);
            $categories_result  = $MainPageObject->categories_result;
            $categories_rownum  = $MainPageObject->categories_rownum;
            $forums_result      = $MainPageObject->forums_result;
            $forums_rownum      = $MainPageObject->forums_rownum;
            $thread             = $MainPageObject->thread;
            $online_result      = $MainPageObject->online_result;
            $online_rownum      = $MainPageObject->online_rownum;

            // View
            $this->view('sketchbookcafe/header');
            $this->view('forum/index',
            [
                'User'              => $User,
                'categories_result' => $categories_result,
                'categories_rownum' => $categories_rownum,
                'forums_result'     => $forums_result,
                'forums_rownum'     => $forums_rownum,
                'thread'            => $thread,
                'online_result'     => $online_result,
                'online_rownum'     => $online_rownum,
            ]);
            $this->view('sketchbookcafe/footer');

        }
        else
        {
            // Objects
            $Member     = $this->obj_array['Member'];
            $Comment    = $this->obj_array['Comment'];

            // Page Numbers
            $pageno = isset ($pageno) ? (int) $pageno : 0;
            if ($pageno < 1)
            {
                $pageno = 0;
            }

            // Model
            $ForumObject    = $this->model('ForumPage');
            $ForumObject->setForumId($forum_id);
            $ForumObject->setPageNumber($pageno);
            $ForumObject->process($this->obj_array);
            $Form           = $ForumObject->Form;
            $category_row   = $ForumObject->category_row;
            $forum_row      = $ForumObject->forum_row;
            $threads_result = $ForumObject->threads_result;
            $threads_rownum = $ForumObject->threads_rownum;
            $view_time      = $ForumObject->view_time;

            $pagenumbers    = $ForumObject->pagenumbers;
            $pages_min      = $ForumObject->pages_min;
            $pages_max      = $ForumObject->pages_max;
            $pages_total    = $ForumObject->pages_total;

            $forum_admin_result = $ForumObject->forum_admin_result;
            $forum_admin_rownum = $ForumObject->forum_admin_rownum;

            // View
            $this->view('sketchbookcafe/header');
            $this->view('forum/forumpage',
            [
                'User'                  => $User,
                'Form'                  => $Form,
                'category_row'          => $category_row,
                'forum_row'             => $forum_row,
                'threads_result'        => $threads_result,
                'threads_rownum'        => $threads_rownum,
                'Member'                => $Member,
                'Comment'               => $Comment,
                'pagenumbers'           => $pagenumbers,
                'pages_min'             => $pages_min,
                'pages_max'             => $pages_max,
                'pages_total'           => $pages_total,
                'view_time'             => $view_time,
                'forum_admin_result'    => $forum_admin_result,
                'forum_admin_rownum'    => $forum_admin_rownum,
                'challenge_row'         => $ForumObject->getChallengeRow(),
            ]);
            $this->view('sketchbookcafe/footer');
        }
    }
}