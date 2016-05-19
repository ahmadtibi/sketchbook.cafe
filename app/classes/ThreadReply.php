<?php
// @author          Kameloh
// @lastUpdated     2016-05-06
// notes: this does not check blocked users so please re-add this to the main files
namespace SketchbookCafe\ThreadReply;

use SketchbookCafe\SBC\SBC as SBC;

class ThreadReply
{
    private $thread_user_id = 0;
    private $thread_id = 0;
    private $challenge_id = 0;
    private $forum_id = 0;

    private $verified = 0;

    private $time = 0;
    private $bump_date = 0;

    private $db;

    // Construct
    public function __construct(&$db)
    {
        $this->db   = &$db;
        $this->time = SBC::getTime();
    }

    // Set Thread ID
    final public function setThreadId($thread_id)
    {
        $method = 'ThreadReply->setThreadId()';

        // Set
        $this->thread_id = isset($thread_id) ? (int) $thread_id : 0;
        if ($this->thread_id < 1)
        {
            SBC::devError('Thread ID is not set',$method);
        }
    }

    // Get Thread User ID
    final public function getThreadUserId()
    {
        return $this->thread_user_id;
    }

    // Check Thread
    final public function checkThread()
    {
        $method = 'ThreadReply->checkThread()';

        if ($this->thread_id < 1)
        {
            SBC::devError('$thread_id is not set',$method);
        }

        // Verify Thread
        $this->verifyThread($this->thread_id);
    }

    // Verify Thread
    final private function verifyThread($thread_id)
    {
        $method = 'ThreadReply->verifyThread()';

        if ($this->verified == 1)
        {
            return null;
        }

        // Initialize
        $db     = &$this->db;

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Thread Info
        $sql = 'SELECT id, challenge_id, forum_id, user_id, is_locked, is_sticky, isdeleted
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
            SBC::devError('Could not find thread in database');
        }

        // Thread locked?
        if ($row['is_locked'] == 1)
        {
            SBC::userError('Thread is locked');
        }

        // Bump Date
        $this->bump_date = $this->time;
        if ($row['is_sticky'] == 1)
        {
            $this->bump_date += 315360000;
        }

        // Other Vars
        $this->thread_user_id   = $row['user_id'];
        $this->challenge_id     = $row['challenge_id'];

        // Forum ID
        $forum_id   = $row['forum_id'];
        if ($forum_id < 1)
        {
            SBC::devError('Forum is not set for thread',$method);
        }
        $this->forum_id = $forum_id;

        // Get Forum Information
        $sql = 'SELECT id, isdeleted
            FROM forums
            WHERE id=?
            LIMIT 1';
        $stmt       = $db->prepare($sql);
        $stmt->bind_param('i',$forum_id);
        $forum_row  = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Verify
        $forum_id   = isset($forum_row['id']) ? (int) $forum_row['id'] : 0;
        if ($forum_id < 1)
        {
            SBC::devError('Thread is not attached to a forum',$method);
        }

        // Not deleted?
        if ($forum_row['isdeleted'] == 1)
        {
            SBC::devError('Forum for this thread no longer exists. Please contact an administrator',$method);
        }   

        // Set as verified
        $this->verified = 1;
    }

    // Verify Comment
    final private function verifyComment($comment_id)
    {
        $method = 'ThreadReply->verifyComment()';

        // Initialize
        $db     = &$this->db;

        // Double check
        if ($comment_id < 1)
        {
            SBC::devError('Comment ID is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Comment Info
        $sql = 'SELECT id, user_id
            FROM sbc_comments
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$comment_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Verify
        $comment_id = isset($row['id']) ? (int) $row['id'] : 0;
        if ($comment_id < 1)
        {
            SBC::devError('Could not find comment in database',$method);
        }

        // Set User ID
        $this->user_id = $row['user_id'];
        if ($this->user_id < 1)
        {
            SBC::devError('User ID is not set for comment',$method);
        }
    }

    // Insert Comment
    final public function insertCommentId($comment_id)
    {
        $method = 'ThreadReply->insertCommentId()';

        // Initialize
        $db         = &$this->db;
        $thread_id  = $this->thread_id;
        if ($thread_id < 1)
        {
            SBC::devError('Thread ID is not set',$method);
        }
        if ($comment_id < 1)
        {
            SBC::devError('Comment ID is not set',$method);
        }

        // Verify Comment
        $this->verifyComment($comment_id);

        // Set again
        $user_id    = $this->user_id;

        // Verify Thread
        $this->verifyThread($thread_id);

        // Switch
        $db->sql_switch('sketchbookcafe_forums');

        // Insert into thread's table
        $table_name = 't'.$thread_id.'d';
        $sql = 'INSERT INTO '.$table_name.'
            SET cid=?,
            uid=?';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ii',$comment_id,$user_id);
        SBC::statementExecute($stmt,$db,$sql,$method);

        // Update Thread's Timer
        $this->updateThread();
    }

    // Update Thread's Timers
    final private function updateThread()
    {
        $method = 'ThreadReply->updateThread()';

        // Initialize
        $db         = &$this->db;
        $time       = $this->time;
        $user_id    = SBC::checkNumber($this->user_id,'$this->user_id');
        $thread_id  = SBC::checkNumber($this->thread_id,'$this->thread_id');
        $forum_id   = SBC::checkNumber($this->forum_id,'$this->forum_id');
        $bump_date  = $this->bump_date;

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Update thread's dates
        $sql = 'UPDATE forum_threads
            SET date_updated=?,
            date_bumped=?,
            last_user_id=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('iiii',$time,$bump_date,$user_id,$thread_id);
        SBC::statementExecute($stmt,$db,$sql,$method);

        // Switch
        $db->sql_switch('sketchbookcafe_forums');

        // Update Forum Table
        $table_name = 'forum'.$forum_id.'x';
        $sql = 'UPDATE '.$table_name.'
            SET date_updated=?,
            date_bumped=?
            WHERE thread_id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('iii',$time,$bump_date,$thread_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }

    // Get Challenge ID
    final public function getChallengeId()
    {
        return $this->challenge_id;
    }
}