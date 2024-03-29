<?php
// @author          Kameloh
// @lastUpdated     2016-05-02
namespace SketchbookCafe\BlockCheck;

use SketchbookCafe\SBC\SBC as SBC;

class BlockCheck
{
    private $user_id = 0;
    private $r_user_id = 0;
    private $hasinfo = 0;

    // Construct
    // $input:  'user_id', 'r_user_id'
    public function __construct($input)
    {
        $method = 'BlockCheck->__construct()';

        // Initialize Vars
        $this->user_id      = isset($input['user_id']) ? (int) $input['user_id'] : 0;
        $this->r_user_id    = isset($input['r_user_id']) ? (int) $input['r_user_id'] : 0;

        // Check
        if ($this->user_id < 1 || $this->r_user_id < 1)
        {
            SBC::devError('$user_id or $r_user_id is not set',$method);
        }

        // Has Info
        $this->hasinfo = 1;
    }

    // Has Info?
    final private function hasInfo()
    {
        $method = 'BlockCheck->hasInfo()';

        // Check if info is set
        if ($this->hasinfo != 1)
        {
            SBC::devError('$hasinfo is not set',$method);
        }
    }

    // Check
    final public function check(&$db)
    {
        $method = 'BlockCheck->check()';

        // Has Info?
        $this->hasInfo();

        // Initialize Vars
        $user_id    = $this->user_id;
        $r_user_id  = $this->r_user_id;

        // Switch
        $db->sql_switch('sketchbookcafe_users');

        // Table Info
        $table_name = 'u'.$user_id.'c';
        $type       = 1; // 1 blocked users
        $cid        = $r_user_id;

        // Check if other user id exists in owner's table
        $sql = 'SELECT id
            FROM '.$table_name.'
            WHERE type=?
            AND cid=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ii',$type,$cid);
        $row  = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Did we find anything?
        $find_id    = isset($row['id']) ? (int) $row['id'] : 0;
        if ($find_id > 0)
        {
            // Generate Error
            SBC::userError('Sorry, you cannot interact with this user (block list)');
        }

        // Check if owner is in the user's table
        $table_name = 'u'.$r_user_id.'c';
        $type       = 1; // 1 blocked users
        $cid        = $user_id;

        // Check if other user id exists in owner's table
        $sql = 'SELECT id
            FROM '.$table_name.'
            WHERE type=?
            AND cid=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('ii',$type,$cid);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Did we find anything?
        $find_id2    = isset($row['id']) ? (int) $row['id'] : 0;
        if ($find_id2 > 0)
        {
            // Generate Error
            SBC::userError('Sorry, you cannot interact with this user (block list)');
        }
    }
}