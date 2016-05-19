<?php
// @author          Kameloh
// @lastUpdated     2016-05-08

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\ForumAdmin\ForumAdmin as ForumAdmin;
use SketchbookCafe\ThreadOrganizer\ThreadOrganizer as ThreadOrganizer;

class ForumThreadSticky
{
    private $time = 0;
    private $comment_id = 0;
    private $thread_id = 0;
    private $user_id = 0;
    private $hasinfo = 0;

    private $obj_array = [];

    // Construct
    public function __construct(&$obj_array)
    {
        $method = 'ForumThreadSticky->__construct()';

        // Set
        $this->obj_array    = &$obj_array;
        $this->time         = SBC::getTime();
    }

    // Set Comment
    final public function setCommentId($comment_id)
    {
        $method = 'ForumThreadSticky->setCommentId()';

        // Set
        $this->comment_id = isset($comment_id) ? (int) $comment_id : 0;
        if ($this->comment_id < 1)
        {
            SBC::devError('$comment_id is not set',$method);
        }

        $this->hasinfo = 1;
    }

    // Has Info
    final public function hasInfo()
    {
        $method = 'ForumThreadSticky->hasInfo()';

        if ($this->hasinfo != 1)
        {
            SBC::devError('hasinfo is not set',$method);
        }
    }

    // Process
    final public function process()
    {
        $method = 'ForumThreadSticky->process()';

        // Has Info
        $this->hasInfo();

        // Initialize
        $db         = &$this->obj_array['db'];
        $User       = &$this->obj_array['User'];
        $comment_id = $this->comment_id;

        // Open Connection
        $db->open();

        // User Required
        $User->required($db);
        $this->user_id = $User->getUserId();

        // Forum Admin
        $ForumAdmin = new ForumAdmin($this->obj_array);
        $ForumAdmin->setUserId($this->user_id);
        $ForumAdmin->getForumInfoByCommentId($comment_id);
        $ForumAdmin->process();
        $ForumAdmin->requireFlag('sticky_thread');
        $this->thread_id = $ForumAdmin->getThreadId();

        // Set Sticky
        $this->setSticky($db);

        // Close Connection
        $db->close();

        // Header
        header('Location: https://www.sketchbook.cafe/forum/thread/'.$this->thread_id.'/');
        exit;
    }

    // Set Sticky
    final private function setSticky(&$db)
    {
        $method = 'ForumThreadSticky->setSticky()';

        // Has Info
        $this->hasInfo();

        // Set
        $thread_id  = SBC::checkNumber($this->thread_id,'$thread_id');
        $time       = $this->time;

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Thread Info
        $sql = 'SELECT id, date_updated, is_sticky, isdeleted
            FROM forum_threads
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$thread_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Check
        $thread_id  = isset($row['id']) ? (int) $row['id'] : 0;
        if ($thread_id < 1)
        {
            SBC::devError('Could not find thread in database',$method);
        }

        // Deleted?
        if ($row['isdeleted'] == 1)
        {
            SBC::userError('Thread no longer exists');
        }

        // Set Vars
        $date_bumped    = $row['date_updated'];
        $is_sticky      = $row['is_sticky'];

        // Set
        if ($is_sticky == 1)
        {
            $is_sticky = 0;
        }
        else
        {
            // Set as sticky and add 10 years to thread
            $is_sticky = 1;
            $date_bumped += 315360000;
        }

        // Update Thread
        $sql = 'UPDATE forum_threads
            SET date_bumped=?,
            is_sticky=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('iii',$date_bumped,$is_sticky,$thread_id);
        SBC::statementExecute($stmt,$db,$sql,$method);

        // Thread Organizer: Update Bump Date for Parent Forum
        $ThreadOrganizer    = new ThreadOrganizer($db);
        $ThreadOrganizer->updateBumpDate($thread_id);
    }
}