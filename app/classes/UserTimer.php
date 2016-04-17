<?php
// User timer based off user's ID instead of IP address

class UserTimer
{
    private $user_id = 0;
    private $column = '';
    private $hasinfo = 0;

    // Construct
    // $input:  'user_id',
    public function __construct($input)
    {
        // Initialize Vars
        $this->user_id = isset($input['user_id']) ? (int) $input['user_id'] : 0;
        if ($this->user_id < 1)
        {
            error('Dev error: $user_id is not set for UserTimer->construct()');
        }
    }

    // Set Column
    final public function setColumn($value)
    {
        // Initialize Vars
        $cooldown   = 0;
        $value      = isset($value) ? $value : '';
        if (empty($value))
        {
            error('Dev error: $value is not set for UserTimer->setColumn()');
        }

        // Switch
        switch ($value)
        {
            case 'message':             $column = 'message';
                                        $cooldown = 5;
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
            error('Dev error: $cooldown is not set for UserTimer->setColumn()');
        }

        // Set Cooldown
        $this->cooldown = $cooldown;

        // Set Column
        $this->column = $column;
        if (empty($this->column))
        {
            error('Could not set column for UserTimer->setColumn()');
        }

        // Has Info
        $this->hasinfo = 1;
    }

    // Has info?
    final private function hasInfo()
    {
        if ($this->hasinfo != 1)
        {
            error('Dev error: $hasinfo is not set for UserTimer->hasInfo()');
        }
    }

    // Check User ID
    final private function checkUserId(&$db)
    {
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
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$user_id);
        if (!$stmt->execute())
        {
            error('Could not execute statement (get user id) for UserTimer->checkUserId()');
        }
        $result = $stmt->get_result();
        $row    = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        $stmt->close();

        // Do we have an ID?
        $id = isset($row['id']) ? $row['id'] : 0;
        if ($id < 1)
        {
            // Insert new ID
            $sql = 'INSERT INTO user_timer
                SET id=?';
            $stmt = $db->prepare($sql);
            $stmt->bind_param('i',$user_id);
            if (!$stmt->execute())
            {
                error('Could not execute statement (insert new id) for UserTimer->checkUserId()');
            }
            $stmt->close();
        }
    }

    // Check Timer
    final public function checkTimer(&$db)
    {
        // Has info?
        $this->hasInfo();

        // Initialize Vars
        $cooldown       = $this->cooldown;
        $column         = $this->column;
        $column_time    = 0;
        $time           = time();
        $user_id        = $this->user_id;

        // Check User ID
        $this->checkUserId($db);

        // Double Check Vars
        if (empty($column) || $user_id < 1 || $time < 1 || $cooldown < 1)
        {
            error('Dev error: checkTimer error: $cooldown: '.$cooldown.', $column: '.$column.', $time: '.$time.', $user_id: '.$user_id);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get the user's current timer
        $sql = 'SELECT '.$column.'
            FROM user_timer
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$user_id);
        if (!$stmt->execute())
        {
            error('Could not execute statement (get user timer) for UserTimer->checkTimer()');
        }
        $result = $stmt->get_result();
        $row    = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        $stmt->close();

        // Set vars
        $column_time    = $row[$column];

        // Time Left
        $time_left      = $time - $column_time;
        if ($time_left < $cooldown)
        {
            error('You must wait at least '.$cooldown.' second(s) before repeating this action.');
        }
    }

    // Update Timer
    final public function update(&$db)
    {
        // Initialize Vars
        $user_id    = $this->user_id;
        $column     = $this->column;
        $time       = time();

        // Check column
        if (empty($column))
        {
            error('Dev error: $column is empty for UserTimer->update()');
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
        if (!$stmt->execute())
        {
            error('Could not execute statement (update user timer) for UserTimer->update()');
        }
        $stmt->close();
    }
}