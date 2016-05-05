<?php
// @author          Kameloh
// @lastUpdated     2016-05-02

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\TableUserForumSub\TableUserForumSub as TableUserForumSub;
use SketchbookCafe\UserOrganizer\UserOrganizer as UserOrganizer;

class ThreadSubscribe
{
    private $thread_id = 0;
    private $user_id = 0;
    private $time = 0;
    private $table_forum_subscriptions = 0;

    // Construct
    public function __construct(&$obj_array)
    {
        $method = 'ThreadSubscribe->__construct()';

        // Initialize
        $db         = &$obj_array['db'];
        $User       = &$obj_array['User'];
        $this->time = SBC::getTime();

        // Thread ID
        $this->thread_id    = isset($_POST['thread_id']) ? (int) $_POST['thread_id'] : 0;
        if ($this->thread_id < 1)
        {
            SBC::devError('$thread_id is not set',$method);
        }

        // Open Connection
        $db->open();

        // User Required
        $User->required($db);
        $this->user_id = $User->getUserId();
        $this->table_forum_subscriptions = $User->getColumn('table_forum_subscriptions');

        // Get Thread Information
        $this->getThreadInfo($db);

        // Check if user has a forum subscription table
        $this->checkUserTable($db);

        // Update subscription for user
        $this->updateSubscriptionTable($db);

        // User Organizer
        $UserOrganizer  = new UserOrganizer($db);

        // Count Total Subscribed Threads
        $UserOrganizer->totalForumSubscriptions($this->user_id);

        // Close Connection
        $db->close();

        // Return user to thread
        header('Location: https://www.sketchbook.cafe/forum/thread/'.$this->thread_id.'/');
        exit;
    }

    // Get Thread Information
    final private function getThreadInfo(&$db)
    {
        $method = 'ThreadSubscribe->getThreadInfo()';

        // Initialize
        $thread_id  = SBC::checkNumber($this->thread_id,'$this->thread_id');

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Thread Info
        $sql = 'SELECT id, isdeleted
            FROM forum_threads
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$thread_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Verify
        $thread_id  = isset($row['id']) ? (int) $row['id'] : 0;
        if ($thread_id < 1)
        {
            SBC::userError('Could not find thread in database');
        }

        // Deleted?
        if ($row['isdeleted'] == 1)
        {
            SBC::userError('Thread no longer exists');
        }
    }

    // User Subscription Table
    final private function checkUserTable(&$db)
    {
        $method = 'ThreadSubscribe->checkUserTable()';

        // Initialize
        $user_id                    = SBC::checkNumber($this->user_id,'$this->user_id');
        $table_forum_subscriptions  = $this->table_forum_subscriptions;

        // Check
        if ($table_forum_subscriptions != 1)
        {
            // Create or check table
            $TableUserForumSub  = new TableUserForumSub($user_id);
            $TableUserForumSub->checkTables($db);

            // Switch
            $db->sql_switch('sketchbookcafe');

            // Update user's table flag
            $sql = 'UPDATE users
                SET table_forum_subscriptions=1
                WHERE id=?
                LIMIT 1';
            $stmt = $db->prepare($sql);
            $stmt->bind_param('i',$user_id);
            SBC::statementExecute($stmt,$db,$sql,$method);
        }
    }

    // Update Subscription Table
    final private function updateSubscriptionTable(&$db)
    {
        $method = 'ThreadSubscribe->updateSubscriptionTable()';

        // Initialize
        $user_id    = SBC::checkNumber($this->user_id,'$this->user_id');
        $thread_id  = SBC::checkNumber($this->thread_id,'$this->thread_id');
        $time       = $this->time;

        // Switch
        $db->sql_switch('sketchbookcafe_users');

        // Table
        $table_name = 'u'.$user_id.'fs';

        // Check if they've already subscribed
        $sql = 'SELECT id
            FROM '.$table_name.'
            WHERE tid=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$thread_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Set ID?
        $id = isset($row['id']) ? (int) $row['id'] : 0;
        if ($id < 1)
        {
            // Check Max Subscriptions
            $this->checkMaxSubscriptions($db);

            // Switch
            $db->sql_switch('sketchbookcafe_users');

            // Add to user's subscription table
            $sql = 'INSERT INTO '.$table_name.'
                SET tid=?,
                pda=?, 
                lda=?';
            $stmt = $db->prepare($sql);
            $stmt->bind_param('iii',$thread_id,$time,$time);
            SBC::statementExecute($stmt,$db,$sql,$method);
        }
        else
        {
            // Remove from user's subscription table
            $sql = 'DELETE FROM '.$table_name.'
                WHERE tid=?
                LIMIT 1';
            $stmt = $db->prepare($sql);
            $stmt->bind_param('i',$thread_id);
            SBC::statementExecute($stmt,$db,$sql,$method);
        }
    }

    // Check max subscriptions
    final private function checkMaxSubscriptions(&$db)
    {
        $method = 'ThreadSubscribe->checkMaxSubscriptions()';

        // Initialize
        $max_subscriptions  = 100;
        $user_id            = SBC::checkNumber($this->user_id,'$this->user_id');

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Check number
        $sql = 'SELECT total_thread_subscriptions
            FROM users
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$user_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Set
        $total  = $row['total_thread_subscriptions'];
        if ($total >= $max_subscriptions)
        {
            SBC::userError('Sorry, you may only subscribe to up to '.$max_subscriptions.' subscriptions');
        }
    }
}