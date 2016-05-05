<?php
// @author          Kameloh
// @lastupdated     2016-04-30

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\PollOrganizer\PollOrganizer as PollOrganizer;

class PollVote
{
    private $vote_id = 0;
    private $thread_id = 0;
    private $poll_id = 0;
    private $poll_option = 0;
    private $user_id = 0;
    private $time = 0;
    private $ip_address = '';
    private $rd = 0;

    // Construct
    public function __construct(&$obj_array)
    {
        $method = 'PollVote->__construct()';

        // Initialize
        $db                 = &$obj_array['db'];
        $User               = &$obj_array['User'];
        $this->rd           = SBC::rd();
        $this->time         = SBC::getTime();
        $this->ip_address   = SBC::getIpAddress();

        // Poll ID
        $this->poll_id = isset($_POST['poll_id']) ? (int) $_POST['poll_id'] : 0;
        if ($this->poll_id < 1)
        {
            SBC::devError('$poll_id is not set',$method);
        }

        // Poll Option
        $this->poll_option = isset($_POST['poll_option']) ? (int) $_POST['poll_option'] : 0;
        if ($this->poll_option < 1 || $this->poll_option > 10)
        {
            SBC::userError('Please select an option');
        }

        // Open Connection
        $db->open();

        // User Required
        $User->required($db);
        $this->user_id = $User->getUserId();

        // Get Poll Info
        $this->getPollInfo($db);

        // Get Thread Info
        $this->getThreadInfo($db);

        // Check if the user already voted on this poll
        $this->checkVote($db);

        // Insert vote into poll table
        $this->insertVote($db);

        $PollOrganizer = new PollOrganizer($db);

        // Poll Organizer: Count Specific Vote Type
        $PollOrganizer->countOption($this->poll_id,$this->poll_option);

        // Poll Organizer: Count Total Votes
        $PollOrganizer->countTotalVotes($this->poll_id);

        // Close Connection
        $db->close();

        // Return to thread
        header('Location: https://www.sketchbook.cafe/forum/thread/'.$this->thread_id.'/');
        exit;
    }

    // Get Poll Information
    final private function getPollInfo(&$db)
    {
        $method = 'PollVote->getPollInfo()';

        // Initialize
        $poll_id        = SBC::checkNumber($this->poll_id,'$this->poll_id');
        $poll_option    = SBC::checkNumber($this->poll_option,'$this->poll_option');

        // Double Check
        if ($poll_option < 1 || $poll_option > 10)
        {
            SBC::devError('invalid $poll_option',$method);
        }
        $column = 'message'.$poll_option;

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Poll
        $sql = 'SELECT id, thread_id, '.$column.', is_locked, is_hidden, isdeleted
            FROM forum_polls
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$poll_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Verify
        $poll_id    = isset($row['id']) ? (int) $row['id'] : 0;
        if ($poll_id < 1)
        {
            SBC::devError('Could not find poll in database',$method);
        }

        // Deleted?
        if ($row['isdeleted'] == 1)
        {
            SBC::userError('Poll no longer exists');
        }

        // Hidden or Locked
        if ($row['is_hidden'] == 1 || $row['is_locked'] == 1)
        {
            SBC::userError('Sorry, this poll is locked/hidden and cannot be voted on');
        }

        // Make sure option is set
        if (empty($row[$column]))
        {
            SBC::userError('Invalid poll option');
        }

        // Set Thread
        $this->thread_id    = $row['thread_id'];
        if ($this->thread_id < 1)
        {
            SBC::devError('$thread_id is not set',$method);
        }
    }

    // Get Thread Info
    final private function getThreadInfo(&$db)
    {
        $method = 'PollVote->getThreadInfo()';

        // Initialize
        $thread_id  = SBC::checkNumber($this->thread_id,'$this->thread_id');

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Thread
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
            SBC::devError('Could not find thread in database',$method);
        }

        // Deleted?
        if ($row['isdeleted'] == 1)
        {
            SBC::userError('Thread no longer exists for poll');
        }
    }

    // Check Vote
    final private function checkVote(&$db)
    {
        $method         = 'PollVote->checkVote()';

        $user_id        = SBC::checkNumber($this->user_id,'$this->user_id');
        $poll_id        = SBC::checkNumber($this->poll_id,'$this->poll_id');
        $poll_option    = SBC::checkNumber($this->poll_option,'$this->poll_option');
        $time           = $this->time;
        $ip_address     = $this->ip_address;
        $rd             = $this->rd;

        $db->sql_switch('sketchbookcafe');

        // Check if the user already voted on this poll
        // temporarily disable this while we test:
        $ignore = 0;
        if ($ignore == 1)
        {
                            $sql = 'SELECT id
                                FROM poll_votes
                                WHERE poll_id=?
                                AND user_id=?
                                AND isdeleted=0
                                LIMIT 1';
                            $stmt   = $db->prepare($sql);
                            $stmt->bind_param('ii',$poll_id,$user_id);
                            $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

                            // Return to thread if the user already voted
                            if ($row['id'] > 0)
                            {
                                $db->close();
                                header('Location: https://www.sketchbook.cafe/forum/thread/'.$this->thread_id.'/');
                                exit;
                            }

        }

        // Insert New Vote
        $sql = 'INSERT INTO poll_votes
            SET rd=?,
            poll_id=?,
            poll_option=?,
            user_id=?,
            date_created=?,
            ip_created=?,
            isdeleted=1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('iiiiis',$rd,$poll_id,$poll_option,$user_id,$time,$ip_address);
        SBC::statementExecute($stmt,$db,$sql,$method);

        // Get Vote ID
        $sql = 'SELECT id 
            FROM poll_votes
            WHERE rd=?
            AND poll_id=?
            AND user_id=?
            AND date_created=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('iiii',$rd,$poll_id,$user_id,$time);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Set
        $vote_id  = isset($row['id']) ? (int) $row['id'] : 0;
        if ($vote_id < 1)
        {
            SBC::devError('Could not get vote ID from database',$method);
        }
        $this->vote_id  = $vote_id;

        // Set vote as not deleted
        $sql = 'UPDATE poll_votes
            SET isdeleted=0
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$vote_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }

    // Insert Vote ID into the poll's table
    final private function insertVote(&$db)
    {
        $method = 'PollVote->insertVote()';

        $user_id        = SBC::checkNumber($this->user_id,'$this->user_id');
        $poll_id        = SBC::checkNumber($this->poll_id,'$this->poll_id');
        $vote_id        = SBC::checkNumber($this->vote_id,'$this->vote_id');
        $poll_option    = SBC::checkNumber($this->poll_option,'$this->poll_option');

        $db->sql_switch('sketchbookcafe_forums');

        $table_name = 'p'.$poll_id.'l';

        // Insert into the table
        $sql = 'INSERT INTO '.$table_name.'
            SET user_id=?,
            vote_id=?,
            poll_option=?';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('iii',$user_id,$vote_id,$poll_option);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }
}