<?php
// Count Content Class : Counts any type of content in the user's tables

class CountContent
{
    private $user_id = 0;

    // Generated
    private $type = '';
    private $hasinfo = 0;

    // Database

    // Construct
    // $input:  'user_id', 'setting'
    public function __construct($input)
    {
        // User ID
        $user_id = isset($input['user_id']) ? (int) $input['user_id'] : 0;
        if ($user_id < 1)
        {
            error('Dev error: $user_id is not set for CountContent->construct()');
        }

        // Setting
        $setting = isset($input['setting']) ? $input['setting'] : '';
        if (empty($setting))
        {
            error('Dev error: $setting is not set for CountContent->construct()');
        }

        // Set Vars
        $this->user_id      = $user_id;

        // Switch Setting
        switch ($setting)
        {
            case 'blocked_users':   $type = 'blocked_users';
                                    break;

            default:                $type   = '';
                                    break;
        }
        if (empty($type))
        {
            error('Dev error: invalid $setting for CountContent->construct()');
        }

        // Create Settings
        $this->type     = $type;
        $this->hasinfo  = 1;
    }

    // Has Info
    final private function hasInfo()
    {
        if ($this->hasinfo != 1)
        {
            error('Dev error: $hasinfo is not set for CountContent->hasInfo()');
        }
    }

    // Process
    final public function process(&$db)
    {
        // Has Info
        $this->hasinfo();

        // Type
        $type = $this->type;

        // Count Total Blocked Users
        if ($type == 'blocked_users')
        {
            $this->countBlockedUsers($db);
        }
    }

    // Count Blocked Users
    final private function countBlockedUsers(&$db)
    {
        // Initialize Vars
        $user_id    = $this->user_id;
        if ($user_id < 1)
        {
            error('Dev error: $user_id is not set for CountContent->countBlockedUsers()');
        }

        // Switch
        $db->sql_switch('sketchbookcafe_users');

        // More Vars
        $table_name = 'u'.$user_id.'c';
        $type       = 1; // 1 blocked users

        // Count
        $sql = 'SELECT COUNT(*)
            FROM '.$table_name.'
            WHERE type=?';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$type);
        if (!$stmt->execute())
        {
            error('Could not execute statement (count from table) for CountContent->countBlockedUsers()');
        }
        $result = $stmt->get_result();
        $row    = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        $stmt->close();

        // Total
        $total = isset($row[0]) ? (int) $row[0] : 0;

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Update user's total blocked
        $sql = 'UPDATE users
            SET blocked_total=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ii',$total,$user_id);
        if (!$stmt->execute())
        {
            error('Could not execute statement (update user total) for CountContent->countBlockedUsers()');
        }
        $stmt->close();
    }
}