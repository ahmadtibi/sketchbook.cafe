<?php
// @author          Kameloh
// @lastUpdated     2016-05-17

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\ForumAdmin\ForumAdmin as ForumAdmin;
use SketchbookCafe\ForumOrganizer\ForumOrganizer as ForumOrganizer;

class ForumThreadDeleteThreadSubmit
{
    private $thread_id = 0;
    private $forum_id = 0;
    private $user_id = 0;
    private $ip_address = '';
    private $time = 0;
    private $poll_id = 0;

    // Construct
    public function __construct(&$obj_array)
    {
        $method = 'ForumThreadDeleteThreadSubmit->__construct()';

        // Initialize
        $db                 = &$obj_array['db'];
        $User               = &$obj_array['User'];
        $this->time         = SBC::getTime();
        $this->ip_address   = SBC::getIpAddress();

        // Post
        $this->thread_id    = isset($_POST['thread_id']) ? (int) $_POST['thread_id'] : 0;
        $action             = isset($_POST['action']) ? (int) $_POST['action'] : 0;
        $confirm            = isset($_POST['confirm']) ? (int) $_POST['confirm'] : 0;
        if ($this->thread_id < 1)
        {
            SBC::devError('Thread ID is not set',$method);
        }

        // Action?
        if ($action != 1)
        {
            // Go back
            header('Location: https://www.sketchbook.cafe/forum/thread/'.$this->thread_id.'/');
            exit;
        }

        // Confirm
        if ($confirm != 1)
        {
            SBC::userError('You must confirm action to continue');
        }

        // Open Connection
        $db->open();

        // User Required
        $User->required($db);
        $this->user_id = $User->getUserId();

        // Forum Admin
        $ForumAdmin = new ForumAdmin($obj_array);
        $ForumAdmin->setUserId($this->user_id);
        $ForumAdmin->getForumInfoByThreadId($this->thread_id);
        $ForumAdmin->process();
        $ForumAdmin->requireFlag('delete_thread');

        // Get Thread Info
        $this->getThreadInfo($db);

        // Delete from Forum
        $this->deleteThreadFromForum($db);

        // Mark Thread as Deleted
        $this->markThreadAsDeleted($db);

        // Mark Poll as Deleted
        $this->markPollAsDeleted($db);

        // Forum Organizer
        $ForumOrganizer = new ForumOrganizer($db);
        $ForumOrganizer->countTotalThreads($this->forum_id);
        $ForumOrganizer->updateLastPostInfo($this->forum_id);

        // Close Connect
        $db->close();

        // Return back to the forum
        header('Location: https://www.sketchbook.cafe/forum/'.$this->forum_id.'/');
        exit;
    }

    // Get Thread Info
    final private function getThreadInfo(&$db)
    {
        $method = 'ForumThreadDeleteThreadSubmit->getThreadInfo()';

        // Initialize
        $thread_id  = SBC::checkNumber($this->thread_id,'$thread_id');

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Thread Info
        $sql = 'SELECT id, challenge_id, poll_id, forum_id, isdeleted
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

        // Note: we are not allowed to delete threads with a challenge attached
        if ($row['challenge_id'] > 0)
        {
            SBC::userError('Sorry, you cannot delete threads that have a challenge attached');
        }

        // Deleted?
        if ($row['isdeleted'] == 1)
        {
            SBC::userError('Thread no longer exists');
        }

        // Set Forum
        $this->forum_id = $row['forum_id'];
        if ($this->forum_id < 1)
        {
            SBC::devError('Forum ID is not set',$method);
        }

        // Set Poll ID
        $this->poll_id = $row['poll_id'];
    }

    // Delete Thread from Forum's Table
    final private function deleteThreadFromForum(&$db)
    {
        $method = 'ForumThreadDeleteThreadSubmit->deleteThreadFromForum()';

        // Initialize
        $forum_id   = SBC::checkNumber($this->forum_id,'$forum_id');
        $thread_id  = SBC::checkNumber($this->thread_id,'$thread_id');

        // Switch
        $db->sql_switch('sketchbookcafe_forums');

        // Table
        $table_name = 'forum'.$forum_id.'x';

        // Delete thread from table
        $sql = 'DELETE FROM '.$table_name.'
            WHERE thread_id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$thread_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }

    // Mark Thread as Deleted
    final private function markThreadAsDeleted(&$db)
    {
        $method = 'ForumThreadDeleteThreadSubmit->markThreadAsDeleted()';

        // Initialize
        $thread_id  = SBC::checkNumber($this->thread_id,'$thread_id');

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Mark thread as deleted
        $sql = 'UPDATE forum_threads
            SET isdeleted=1
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$thread_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }

    // Mark Poll as Deleted
    final private function markPollAsDeleted(&$db)
    {
        $method = 'ForumThreadDeleteThreadSubmit->markPollAsDeleted()';

        $poll_id    = $this->poll_id;
        if ($poll_id < 1)
        {
            return null;
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Update Poll as Deleted
        $sql = 'UPDATE forum_polls
            SET isdeleted=1
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$poll_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }
}