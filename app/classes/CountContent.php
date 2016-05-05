<?php
// @author          Kameloh
// @lastUpdated     2016-04-27
// Count Content Class : Counts any type of content in the user's tables
namespace SketchbookCafe\CountContent;

use SketchbookCafe\SBC\SBC as SBC;

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
        $method = 'CountContent->__construct()';

        // User ID
        $user_id = isset($input['user_id']) ? (int) $input['user_id'] : 0;
        if ($user_id < 1)
        {
            SBC::devError('$user_id is not set',$method);
        }

        // Setting
        $setting = isset($input['setting']) ? $input['setting'] : '';
        if (empty($setting))
        {
            SBC::devError('$setting is not set',$method);
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
            SBC::devError('invalid $setting',$method);
        }

        // Create Settings
        $this->type     = $type;
        $this->hasinfo  = 1;
    }

    // Has Info
    final private function hasInfo()
    {
        $method = 'CountContent->hasInfo()';

        if ($this->hasinfo != 1)
        {
            SBC::devError('$hasinfo is not set',$method);
        }
    }

    // Process
    final public function process(&$db)
    {
        $method = 'CountContent->process()';

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
        $method = 'CountContent->countBlockedUsers()';

        // Initialize Vars
        $user_id    = $this->user_id;
        if ($user_id < 1)
        {
            SBC::devError('$user_id is not set',$method);
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
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$type);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

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
        SBC::statementExecute($stmt,$db,$sql,$method);
    }
}