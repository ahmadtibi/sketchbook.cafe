<?php
// Single Forum

class Forum extends Controller
{
    protected $obj_array = '';

    public function __construct(&$obj_array)
    {
        $this->obj_array = &$obj_array;
    }

    // Forum Thread
    public function thread($thread_id = 0, $pageno = 0)
    {
        // Objects
        $User       = $this->obj_array['User'];
        $Comment    = $this->obj_array['Comment'];
        $Member     = $this->obj_array['Member'];

        // Thread ID
        $thread_id  = isset($thread_id) ? (int) $thread_id : 0;
        if ($thread_id < 1)
        {
            error('Invalid Thread ID');
        }

        // Page Numbers
        $pageno     = isset($pageno) ? (int) $pageno : 0;
        if ($pageno < 1)
        {
            $pageno = 0;
        }

        // Model
        $ThreadObject       = $this->model('ForumThreadPage');
        $ThreadObject->setThreadId($thread_id);
        $ThreadObject->setPageNumber($pageno);
        $ThreadObject->process($this->obj_array);
        $thread_row         = $ThreadObject->thread_row;
        $category_row       = $ThreadObject->category_row;
        $forum_row          = $ThreadObject->forum_row;
        $Form               = $ThreadObject->Form;
        $comments_result    = $ThreadObject->comments_result;
        $comments_rownum    = $ThreadObject->comments_rownum;
        $pagenumbers        = $ThreadObject->pagenumbers;
        $pages_min          = $ThreadObject->pages_min;
        $pages_max          = $ThreadObject->pages_max;
        $pages_total        = $ThreadObject->pages_total;

        // View
        $this->view('sketchbookcafe/header');
        $this->view('forum/threadpage',
        [
            'User'              => $User,
            'thread_row'        => $thread_row,
            'category_row'      => $category_row,
            'forum_row'         => $forum_row,
            'Comment'           => $Comment,
            'Member'            => $Member,
            'Form'              => $Form,
            'comments_result'   => $comments_result,
            'comments_rownum'   => $comments_rownum,
            'pagenumbers'       => $pagenumbers,
            'pages_min'         => $pages_min,
            'pages_max'         => $pages_max,
            'pages_total'       => $pages_total,
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
    public function index($forum_id = 0)
    {
        // Objects
        $User       = $this->obj_array['User'];
        $Member     = $this->obj_array['Member'];
        $Comment    = $this->obj_array['Comment'];

        // Initialize Vars
        $forum_id = isset($forum_id) ? (int) $forum_id : 0;
        if ($forum_id < 1)
        {
            error('Forum ID is not set');
        }

        // Model
        $ForumObject    = $this->model('ForumPage');
        $ForumObject->setForumId($forum_id);
        $ForumObject->process($this->obj_array);
        $Form           = $ForumObject->Form;
        $category_row   = $ForumObject->category_row;
        $forum_row      = $ForumObject->forum_row;
        $threads_result = $ForumObject->threads_result;
        $threads_rownum = $ForumObject->threads_rownum;

        // View
        $this->view('sketchbookcafe/header');
        $this->view('forum/forumpage',
        [
            'User'              => $User,
            'Form'              => $Form,
            'category_row'      => $category_row,
            'forum_row'         => $forum_row,
            'threads_result'    => $threads_result,
            'threads_rownum'    => $threads_rownum,
            'Member'            => $Member,
            'Comment'           => $Comment,
        ]);
        $this->view('sketchbookcafe/footer');

    }


}