<?php
// @author          Kameloh
// @lastUpdated     2016-05-06
namespace SketchbookCafe\ForumThread;

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\Challenge\Challenge as Challenge;

class ForumThread
{
    // User Info
    private $time = 0;
    private $user_id = 0;
    public $subscribed = 0;
    public $subscription_lda = 0; // last updated
    private $table_forum_subscriptions = 0;

    // Thread Info
    private $forum_id = 0;
    private $thread_id = 0;
    private $thread_date_updated = 0;
    private $total_comments = 0;
    public $challenge_id = 0;
    public $poll_id = 0;

    // Comments
    public $comments_result;
    public $comments_rownum = 0;

    // Page Numbers
    private $pageno = 0;
    private $ppage = 10;

    // Data Arrays
    public $thread_row = [];
    public $forum_row = [];
    public $category_row = [];
    public $poll_row = [];
    public $challenge_row = [];

    private $obj_array;

    // Construct
    public function __construct(&$obj_array)
    {
        $this->obj_array    = &$obj_array;
        $this->time         = SBC::getTime();
    }

    // Set Thread ID
    final public function setThreadId($thread_id)
    {
        $method = 'ForumThread->setThreadId()';

        $this->thread_id = isset($thread_id) ? (int) $thread_id : 0;
        if ($this->thread_id < 1)
        {
            SBC::devError('$thread_id is not set',$method);
        }
    }

    // Set User ID
    final public function setUserId($user_id)
    {
        $method = 'ForumThread->setUserId()';

        $this->user_id = isset($user_id) ? (int) $user_id : 0;
        if ($this->user_id < 1)
        {
            $this->user_id = 0;
        }
    }

    // Set Subscription Table
    final public function setSubscriptionTable($value)
    {
        $this->table_forum_subscriptions = isset($value) ? (int) $value : 0;
    }

    // Set Page Number
    final public function setPageNumber($pageno)
    {
        $this->pageno = isset($pageno) ? (int) $pageno : 0;
        if ($this->pageno < 1)
        {
            $this->pageno = 0;
        }
    }

    // Process Thread
    final public function process()
    {
        $method = 'ForumThread->process()';

        // Initialize
        $db         = &$this->obj_array['db'];
        $Member     = &$this->obj_array['Member'];
        $Comment    = &$this->obj_array['Comment'];
        $thread_id  = $this->thread_id;

        // Check
        if ($thread_id < 1)
        {
            SBC::devError('$thread_id is not set',$method);
        }

        // Get Thread Information
        $sql = 'SELECT id, challenge_id, poll_id, forum_id, user_id, date_created, date_updated, 
            comment_id, title, total_comments, is_poll, is_locked, is_sticky, isdeleted
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

        // Deleted?
        if ($thread_row['isdeleted'] == 1)
        {
            SBC::userError('Thread no longer exists');
        }

        // Forum
        $forum_id   = $thread_row['forum_id'];
        if ($forum_id < 1)
        {
            SBC::devError('Thread does not have a forum set',$method);
        }

        // Get Forum Information
        $sql = 'SELECT id, parent_id, name
            FROM forums
            WHERE id=?
            LIMIT 1';
        $stmt       = $db->prepare($sql);
        $stmt->bind_param('i',$forum_id);
        $forum_row  = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Verify
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

        // Set vars
        $this->thread_date_updated  = $thread_row['date_updated'];
        $this->total_comments       = $thread_row['total_comments'];
        $this->challenge_id         = $thread_row['challenge_id'];
        $this->poll_id              = $thread_row['poll_id'];
        $this->forum_id             = $thread_row['forum_id'];

        // Set Arrays
        $this->thread_row           = $thread_row;
        $this->forum_row            = $forum_row;
        $this->category_row         = $category_row;

        // Add Member's User ID
        $Member->idAddOne($thread_row['user_id']);

        // Add Comment ID
        $Comment->idAddOne($thread_row['comment_id']);

        // Check Subscribed
        $this->checkSubscribed();

        // Get Poll
        $this->getPoll();

        // Get Challenge
        $this->getChallenge();

        // Get Comments
        $this->getComments();
    }

    // Check Subscribed
    final private function checkSubscribed()
    {
        $method = 'ForumThread->checkSubscribed()';

        // Initialize
        $db                     = &$this->obj_array['db'];
        $time                   = $this->time;
        $user_id                = $this->user_id;
        $thread_id              = $this->thread_id;
        $thread_date_updated    = $this->thread_date_updated;
        $table_name             = 'u'.$user_id.'fs';
        if ($user_id < 1 || $thread_id < 1 || $this->table_forum_subscriptions < 1)
        {
            return null;
        }

        // Switch
        $db->sql_switch('sketchbookcafe_users');

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

    // Get Comments
    final private function getComments()
    {
        $method = 'ForumThread->getComments()';

        // Initialize
        $db         = &$this->obj_array['db'];
        $Comment    = &$this->obj_array['Comment'];
        $thread_id  = $this->thread_id;
        $ppage      = $this->ppage;
        $pageno     = $this->pageno;
        $offset     = $pageno * $ppage;
        $table_name = 't'.$thread_id.'d';
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

        // Get Comments
        $sql = 'SELECT cid
            FROM '.$table_name.'
            ORDER BY id
            ASC
            LIMIT '.$offset.', '.$ppage;
        $this->comments_result = $db->sql_query($sql);
        $this->comments_rownum = $db->sql_numrows($this->comments_result);

        // Add Comment IDs
        $Comment->idAddRows($this->comments_result,'cid');
    }

    // Get Poll Info
    final private function getPoll()
    {
        $method = 'ForumThread->getPoll()';

        $poll_id    = $this->poll_id;
        if ($poll_id < 1)
        {
            return null;
        }

        // Initialize
        $db     = &$this->obj_array['db'];

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

    // Get Challenge
    final private function getChallenge()
    {
        $method = 'ForumThread->getChallenge()';

        // Initialize
        $challenge_id   = $this->challenge_id;
        if ($challenge_id < 1)
        {
            return null;
        }
        $db             = &$this->obj_array['db'];

        // Challenge
        $Challenge  = new Challenge($db);
        $Challenge->setChallengeId($challenge_id);
        $Challenge->process();
        $this->challenge_row = $Challenge->challenge_row;
    }

    // Get Total Comments
    final public function getTotalComments()
    {
        return $this->total_comments;
    }

    // Get Forum ID
    final public function getForumId()
    {
        return $this->forum_id;
    }
}