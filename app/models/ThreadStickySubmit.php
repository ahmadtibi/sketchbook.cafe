<?php
// @author          Kameloh
// @lastUpdated     2016-04-29

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\ForumAdmin\ForumAdmin as ForumAdmin;
use SketchbookCafe\ForumOrganizer\ForumOrganizer as ForumOrganizer;

class ThreadStickySubmit
{
    private $user_id = 0;
    private $thread_id = 0;
    private $is_sticky = 0;

    // Construct
    public function __construct(&$obj_array)
    {
        $method = 'ThreadStickySubmit->__construct()';

        // Initialize
        $db                 = &$obj_array['db'];
        $User               = &$obj_array['User'];
        $this->thread_id    = SBC::checkNumber($_POST['thread_id'],'$thread_id');
        $action             = SBC::oneZero($_POST['action']);
        if ($action != 1)
        {
            // Go back
            header('Location: https://www.sketchbook.cafe/forum/thread/'.$this->thread_id.'/');
            exit;
        }

        // Open Connection
        $db->open();

        // User Required
        $User->required($db);
        $this->user_id  = $User->getUserId();

        // Forum Admin
        $ForumAdmin = new ForumAdmin($obj_array);
        $ForumAdmin->setUserId($this->user_id);
        $ForumAdmin->getForumInfoByThreadId($this->thread_id);
        $ForumAdmin->process();
        $ForumAdmin->requireFlag('sticky_thread');

        // Get Thread Info
        $this->getThread($db);

        // Set Sticky
        $this->setSticky($db);

        // Close Connection
        $db->close();

        // Header
        header('Location: https://www.sketchbook.cafe/forum/thread/'.$this->thread_id.'/');
        exit;
    }

    // Has Info
    final private function hasInfo()
    {
        $method = 'ThreadStickySubmit->hasInfo()';

        if ($this->thread_id < 1)
        {
            SBC::devError('$thread_id is not set',$method);
        }
    }

    // Get Thread
    final private function getThread(&$db)
    {
        $method = 'ThreadStickySubmit->getThread()';

        // Has info?
        $this->hasInfo();

        // Set
        $thread_id  = $this->thread_id;

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Thread Info
        $sql = 'SELECT id, is_sticky, isdeleted
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

        // Set Is Sticky
        $this->is_sticky = $row['is_sticky'];
    }

    // Set Sticky
    final private function setSticky(&$db)
    {
        $method = 'ThreadStickySubmit->setSticky()';

        // Has info?
        $this->hasInfo();

        // Initialize
        $thread_id  = $this->thread_id;
        $is_sticky  = $this->is_sticky;

        // Set
        if ($is_sticky == 1)
        {
            $is_sticky = 0;
        }
        else
        {
            $is_sticky = 1;
        }

        // Change time if sticky
        $time   = SBC::getTime();
        if ($is_sticky == 1)
        {
            // Add 10 years (315360000s) to bump_date
            $time += 315360000;
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Update Thread
        $sql = 'UPDATE forum_threads
            SET date_bumped=?,
            is_sticky=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('iii',$time,$is_sticky,$thread_id);
        SBC::statementExecute($stmt,$db,$sql,$method);

        // Forum Organizer
        $ForumOrganizer = new ForumOrganizer($db);
        $ForumOrganizer->threadUpdateBumpDate($thread_id);
    }
}