<?php
// @author          Jonathan Maltezo (Kameloh)
// @lastUpdated     2016-04-27

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\UserTimer\UserTimer as UserTimer;
use SketchbookCafe\CountContent\CountContent as CountContent;
use SketchbookCafe\SBCGetUsername\SBCGetUsername as SBCGetUsername;

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
        $method = 'BlockUserSubmit->__construct()';

        // Initialize Objects
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // Initialize Vars
        $this->time         = SBC::getTime();
        $r_username         = '';
        $r_username         = SBCGetUsername::process($_POST['username']);
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
        $method = 'BlockUserSubmit->checkTotal()';

        // Initialize Vars
        $user_id    = $this->user_id;
        if ($user_id < 1)
        {
            SBC::devError('$user_id is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get total blocked MAX
        $sql = 'SELECT blocked_max
            FROM users
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$user_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Blocked Max
        $blocked_max = isset($row['blocked_max']) ? (int) $row['blocked_max'] : 0;
        if ($blocked_max < 1)
        {
            SBC::devError('$blocked_max is not set',$method);
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
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$type);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Total
        $total = isset($row[0]) ? (int) $row[0] : 0;

        // Max Check
        if ($total >= $blocked_max)
        {
            SBC::userError('Sorry, you may only block up to '.$blocked_max.' users at a time');
        }
    }

    // Block User
    final private function blockUser(&$db)
    {
        $method = 'BlockUserSubmit->blockUser()';

        // Initialize Vars
        $time       = $this->time;
        $user_id    = $this->user_id;
        $r_user_id  = $this->r_user_id;
        if ($user_id < 1 || $r_user_id < 1 || $user_id == $r_user_id)
        {
            SBC::devError('Invalid $user_id or $r_user_id ($user_id:'.$user_id.', $r_user_id:'.$r_user_id.')',$method);
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
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('ii',$type,$cid);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

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
            SBC::statementExecute($stmt,$db,$sql,$method);
        }
    }

    // Get User Information
    final private function getUserInformation(&$db)
    {
        $method = 'BlockUserSubmit->getUserInformation()';

        // Initialize Vars
        $r_username = $this->r_username;
        $r_user_id  = 0;
        $user_id    = $this->user_id;
        if (empty($r_username))
        {
            SBC::devError('$r_username is not set',$method);
        }
        if ($user_id < 1)
        {
            SBC::devError('$user_id is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get user information
        $sql = 'SELECT id
            FROM users
            WHERE username=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('s',$r_username);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Set
        $r_user_id = isset($row['id']) ? (int) $row['id'] : 0;
        if ($r_user_id < 1)
        {
            SBC::userError('Could not find user in database');
        }

        // Make sure user can't block themselves!
        if ($r_user_id == $user_id)
        {
            SBC::userError('Sorry, you cannot block yourself');
        }

        // Set vars
        $this->r_user_id = $r_user_id;
    }
}