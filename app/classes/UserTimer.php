<?php
// User timer based off user's ID instead of IP address
// Last Updated     2016-04-26
namespace SketchbookCafe\UserTimer;

use SketchbookCafe\SBC\SBC as SBC;

class UserTimer
{
    private $user_id = 0;
    private $column = '';
    private $hasinfo = 0;

    // Construct
    // $input:  'user_id',
    public function __construct($input)
    {
        $method = 'UserTimer->__construct()';

        // Initialize Vars
        $this->user_id = isset($input['user_id']) ? (int) $input['user_id'] : 0;
        if ($this->user_id < 1)
        {
            SBC::devError('$user_id is not set',$method);
        }
    }

    // Set Column
    final public function setColumn($value)
    {
        $method = 'UserTimer->setColumn()';

        // Initialize Vars
        $cooldown   = 0;
        $value      = isset($value) ? $value : '';
        if (empty($value))
        {
            SBC::devError('$value is not set',$method);
        }

        // Switch
        switch ($value)
        {
            case 'forum_reply':         $column = 'forum_reply';
                                        $cooldown = 5; // 5 second flood
                                        break;

            case 'edit_comment':        $column = 'edit_comment';
                                        $cooldown = 5; // 5 seconds for editing comments
                                        break;

            case 'new_forum_thread':    $column = 'new_forum_thread';
                                        $cooldown = 30; // 30 second flood limit? might lower this
                                        break;

            case 'message':             $column = 'message';
                                        $cooldown = 3;
                                        break;

            case 'compose_note':        $column = 'compose_note';
                                        $cooldown = 30; // 30 seconds
                                        break;

            case 'change_avatar':       $column = 'change_avatar';
                                        $cooldown = 30; // 30 seconds
                                        break;

            case 'change_email':        $column = 'change_email';
                                        $cooldown = 900; // 900 - 15 minutes
                                        break;

            case 'change_password':     $column = 'change_password';
                                        $cooldown = 900; // 900 = 15 minutes
                                        break;

            case 'action':              $column = 'action';
                                        $cooldown = 2; // 2 seconds
                                        break;

            default:                    $column = '';
                                        $cooldown = 0;
                                        break;
        }

        // Cooldown Check
        if ($cooldown < 1)
        {
            SBC::devError('$cooldown is not set',$method);
        }

        // Set Cooldown
        $this->cooldown = $cooldown;

        // Set Column
        $this->column = $column;
        if (empty($this->column))
        {
            SBC::devError('Could not set column',$method);
        }

        // Has Info
        $this->hasinfo = 1;
    }

    // Has info?
    final private function hasInfo()
    {
        $method = 'UserTimer->hasInfo()';

        if ($this->hasinfo != 1)
        {
            SBC::devError('$hasinfo is not set',$method);
        }
    }

    // Check User ID
    final private function checkUserId(&$db)
    {
        $method = 'UserTimer->checkUserId()';

        // Has info?
        $this->hasInfo();

        // Initialize Vars
        $user_id    = $this->user_id;

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Check
        $sql = 'SELECT id
            FROM user_timer
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$user_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Do we have an ID?
        $id = isset($row['id']) ? $row['id'] : 0;
        if ($id < 1)
        {
            // Insert new ID
            $sql = 'INSERT INTO user_timer
                SET id=?';
            $stmt = $db->prepare($sql);
            $stmt->bind_param('i',$user_id);
            SBC::statementExecute($stmt,$db,$sql,$method);
        }
    }

    // Check Timer
    final public function checkTimer(&$db)
    {
        $method = 'UserTimer->checkTimer()';

        // Has info?
        $this->hasInfo();

        // Initialize Vars
        $cooldown       = $this->cooldown;
        $column         = $this->column;
        $column_time    = 0;
        $time           = SBC::getTime();
        $user_id        = $this->user_id;

        // Check User ID
        $this->checkUserId($db);

        // Double Check Vars
        if (empty($column) || $user_id < 1 || $time < 1 || $cooldown < 1)
        {
            SBC::devError('$cooldown: '.$cooldown.', $column: '.$column.', $time: '.$time.', $user_id: '.$user_id, $method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get the user's current timer
        $sql = 'SELECT '.$column.'
            FROM user_timer
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$user_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Set vars
        $column_time    = $row[$column];

        // Time Left
        $time_left      = $time - $column_time;
        if ($time_left < $cooldown)
        {
            SBC::userError('You must wait at least '.$cooldown.' second(s) before repeating this action.');
        }
    }

    // Update Timer
    final public function update(&$db)
    {
        $method = 'UserTimer->update()';

        // Initialize Vars
        $user_id    = $this->user_id;
        $column     = $this->column;
        $time       = time();

        // Check column
        if (empty($column))
        {
            SBC::devError('$column is empty',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Update User Timer
        $sql = 'UPDATE user_timer
            SET '.$column.'=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ii',$time,$user_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }
}