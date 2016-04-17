<?php

class BlockUserSubmit
{
    private $user_id = 0;
    private $time = 0;

    // Other User
    private $r_username = '';
    private $r_user_id = 0;

    // Construct
    public function __construct(&$obj_array)
    {
        // Initialize Objects
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // Classes + Functions
        sbc_class('UserTimer');
        sbc_class('CountContent');
        sbc_function('get_username');

        // Initialize Vars
        $this->time         = time();

        // Get Username
        $r_username         = '';
        $r_username         = get_username($_POST['username']);
        $this->r_username   = $r_username;

        // Open Connection
        $db->open();

        // User Required
        $User->required($db);
        $user_id        = $User->getUserId();
        $this->user_id  = $user_id;

        // User Timer
        $UserTimer = new UserTimer(array
        (
            'user_id'   => $user_id, 
        ));
        $UserTimer->setColumn('action');
        $UserTimer->checkTimer($db);

        // Total Check
        $this->checkTotal($db);

        // Get Other User Information
        $this->getUserInformation($db);

        // Block User
        $this->blockUser($db);

        // Count Content
        $CountContent = new CountContent(array
        (
            'user_id'   => $user_id,
            'setting'   => 'blocked_users',
        ));
        $CountContent->process($db);

        // User Timer
        $UserTimer->update($db);

        // Close Connection
        $db->close();

        // Header
        header('Location: https://www.sketchbook.cafe/settings/blockuser/');
        exit;
    }

    // Check Total Blocked Users
    final private function checkTotal(&$db)
    {
        // Initialize Vars
        $user_id    = $this->user_id;
        if ($user_id < 1)
        {
            error('Dev error: $user_id is not set for BlockUserSubmit->checkTotal()');
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get total blocked MAX
        $sql = 'SELECT blocked_max
            FROM users
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$user_id);
        if (!$stmt->execute())
        {
            error('Could not execute statement (get blocked max) for BlockUserSubmit->checkTotal()');
        }
        $result = $stmt->get_result();
        $row    = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        $stmt->close();

        // Blocked Max
        $blocked_max = isset($row['blocked_max']) ? (int) $row['blocked_max'] : 0;
        if ($blocked_max < 1)
        {
            error('Dev error: $blocked_max is not set for BlockuserSubmit->checkTotal()');
        }

        // Switch
        $db->sql_switch('sketchbookcafe_users');

        // Set vars
        $table_name = 'u'.$user_id.'c';
        $type       = 1; // blocked

        // Count blocked since we shouldn't trust the stored count
        $sql = 'SELECT COUNT(*)
            FROM '.$table_name.'
            WHERE type=?';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$type);
        if (!$stmt->execute())
        {
            error('Could not execute statement (count from table) for BlockUserSubmit->checkTotal()');
        }
        $result = $stmt->get_result();
        $row    = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        $stmt->close();

        // Total
        $total = isset($row[0]) ? (int) $row[0] : 0;

        // Max Check
        if ($total >= $blocked_max)
        {
            error('Sorry, you may only block up to '.$blocked_max.' users at a time');
        }
    }

    // Block User
    final private function blockUser(&$db)
    {
        // Initialize Vars
        $time       = $this->time;
        $user_id    = $this->user_id;
        $r_user_id  = $this->r_user_id;
        if ($user_id < 1 || $r_user_id < 1 || $user_id == $r_user_id)
        {
            error('Dev error: Invalid $user_id or $r_user_id ($user_id:'.$user_id.', $r_user_id:'.$r_user_id.') in BlockUserSubmit->blockuser()');
        }

        // Switch
        $db->sql_switch('sketchbookcafe_users');

        // Set Vars
        $table_name = 'u'.$user_id.'c';
        $type       = 1; // type 1: blocked users
        $cid        = $r_user_id;

        // Check if the user exists in owner's table
        $sql = 'SELECT id
            FROM '.$table_name.'
            WHERE type=?
            AND cid=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ii',$type,$cid);
        if (!$stmt->execute())
        {
            error('Could not execute statement (check owner table) for BlockUserSubmit->blockUser()');
        }
        $result = $stmt->get_result();
        $row    = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        $stmt->close();

        // Current ID?
        $current_id = isset($row['id']) ? (int) $row['id'] : 0;
        if ($current_id < 1)
        {
            // Add new user
            $sql = 'INSERT INTO '.$table_name.'
                SET type=?,
                cid=?';
            $stmt = $db->prepare($sql);
            $stmt->bind_param('ii',$type,$cid);
            if (!$stmt->execute())
            {
                error('Could not execute statement (add new user) for BlockUserSubmit->blockUser()');
            }
            $stmt->close();
        }
    }

    // Get User Informatoin
    final private function getUserInformation(&$db)
    {
        // Initialize Vars
        $r_username = $this->r_username;
        $r_user_id  = 0;
        $user_id    = $this->user_id;
        if (empty($r_username))
        {
            error('Dev error: $r_username is not set for BlockUserSubmit->getUserInformation()');
        }
        if ($user_id < 1)
        {
            error('Dev error: $user_id is not set for BlockUserSubmit->getUserInformation()');
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get user information
        $sql = 'SELECT id
            FROM users
            WHERE username=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('s',$r_username);
        if (!$stmt->execute())
        {
            error('Could not execute statement (get user information) for BlockUserSubmit->getUserInformation()');
        }
        $result = $stmt->get_result();
        $row    = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        $stmt->close();

        // Set
        $r_user_id = isset($row['id']) ? (int) $row['id'] : 0;
        if ($r_user_id < 1)
        {
            error('Could not find user in database');
        }

        // Make sure user can't block themselves!
        if ($r_user_id == $user_id)
        {
            error('Sorry, you cannot block yourself');
        }

        // Set vars
        $this->r_user_id = $r_user_id;
    }
}