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
    public function thread($thread_id = 0)
    {
        // Objects
        $User       = $this->obj_array['User'];
        $Comment    = $this->obj_array['Comment'];
        $Member     = $this->obj_array['Member'];

        // Thread ID
        $thread_id = isset($thread_id) ? (int) $thread_id : 0;
        if ($thread_id < 1)
        {
            error('Invalid Thread ID');
        }

        // Model
        $ThreadObject   = $this->model('ForumThreadPage');
        $ThreadObject->setThreadId($thread_id);
        $ThreadObject->process($this->obj_array);
        $thread_row     = $ThreadObject->thread_row;
        $category_row   = $ThreadObject->category_row;
        $forum_row      = $ThreadObject->forum_row;
        $Form           = $ThreadObject->Form;

        // View
        $this->view('sketchbookcafe/header');
        $this->view('forum/threadpage',
        [
            'User'          => $User,
            'thread_row'    => $thread_row,
            'category_row'  => $category_row,
            'forum_row'     => $forum_row,
            'Comment'       => $Comment,
            'Member'        => $Member,
            'Form'          => $Form,
        ]);
        $this->view('sketchbookcafe/footer');
    }
    public function thread_reply()
    {
        // That boy needs therapy!
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
        $db     = $this->obj_array['db'];
        $User   = $this->obj_array['User'];

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

        // View
        $this->view('sketchbookcafe/header');
        $this->view('forum/forumpage',
        [
            'User'              => $User,
            'Form'              => $Form,
            'category_row'      => $category_row,
            'forum_row'         => $forum_row,
        ]);
        $this->view('sketchbookcafe/footer');

    }


}