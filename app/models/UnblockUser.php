<?php

class UnblockUser
{
    private $user_id = 0;
    private $r_user_id = 0;

    // Construct
    public function __construct()
    {
    }

    // Set Other User Id
    final public function setUserId($id)
    {
        // Initialize Vars
        $r_user_id = isset($id) ? (int) $id : 0;
        if ($r_user_id < 1)
        {
            error('Dev error: $r_user_id is not set for UnblockUser->setUserId()');
        }

        // Set Vars
        $this->r_user_id = $r_user_id;
    }

    // Unblock user
    final public function unblockUser(&$obj_array)
    {
        // Initialize Objects
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // Classes
        sbc_class('CountContent');

        // Initialize Vars
        $r_user_id = $this->r_user_id;
        if ($r_user_id < 1)
        {
            error('Dev error: $r_user_id is not set for UnblockUser->unblockUser()');
        }

        // Open Connection
        $db->open();

        // User Required
        $User->required($db);
        $user_id        = $User->getUserId();
        $this->user_id  = $user_id;

        // Verify Info
        if ($user_id < 1)
        {
            error('Dev error: $user_id is not set for UnblockUser->unblockUser()');
        }

        // Switch
        $db->sql_switch('sketchbookcafe_users');

        // Set Vars
        $table_name = 'u'.$user_id.'c';
        $type       = 1; // 1 blocked users
        $cid        = $r_user_id;

        // Just delete
        $sql = 'DELETE FROM '.$table_name.'
            WHERE type=?
            AND cid=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ii',$type,$cid);
        if (!$stmt->execute())
        {
            error('Could not execute statement (delete from user table) in UnblockUser->unblockUser()');
        }
        $stmt->close();

        // Count Content
        $CountContent = new CountContent(array
        (
            'user_id'   => $user_id,
            'setting'   => 'blocked_users',
        ));
        $CountContent->process($db);

        // Close Connection
        $db->close();

        // Header
        header('Location: https://www.sketchbook.cafe/settings/blockuser/');
        exit;
    }
}