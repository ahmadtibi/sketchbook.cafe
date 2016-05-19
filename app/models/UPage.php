<?php
// @author          Kameloh
// @lastUpdated     2016-05-16

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\SBCGetUsername\SBCGetUsername as SBCGetUsername;

class UPage
{
    private $entries_result = [];
    private $entries_rownum = 0;
    private $user_row = [];
    private $profile_user_id = 0;
    private $profile_username = 0;
    private $obj_array = [];

    // Construct
    public function __construct(&$obj_array)
    {
        $this->obj_array = &$obj_array;
    }

    // Set Username
    final public function setUsername($username)
    {
        $method = 'UPage->setUsername()';

        $this->profile_username = SBCGetUsername::process($username);
        if (empty($this->profile_username))
        {
            SBC::devError('Username is not set',$method);
        }
    }

    // Process
    final public function process()
    {
        $method = 'UPage->process()';

        // Initialize
        $db     = &$this->obj_array['db'];
        $User   = &$this->obj_array['User'];

        // Open Connection
        $db->open();

        // User Optional
        $User->setFrontpage();
        $User->optional($db);

        // Get User Info
        $this->getUserInfo($db);

        // Get User Gallery (entries only at the moment)
        $this->getEntriesGallery($db);

        // Process All Data
        $ProcessAllData = new ProcessAllData();

        // Close Connection
        $db->close();
    }

    // Get User Info
    final private function getUserInfo(&$db)
    {
        $method = 'UPage->getUserInfo()';

        // Initialize
        $Member             = &$this->obj_array['Member'];
        $profile_username   = $this->profile_username;
        if (empty($profile_username))
        {
            SBC::devError('Username is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get user
        $sql = 'SELECT id, username, avatar_url, date_registered, date_lastlogin, aboutme, title, 
            sketch_points, total_posts, total_entries
            FROM users
            WHERE username=?
            LIMIT 1';
        $stmt           = $db->prepare($sql);
        $stmt->bind_param('s',$profile_username);
        $this->user_row = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Verify
        $profile_user_id = isset($this->user_row['id']) ? (int) $this->user_row['id'] : 0;
        if ($profile_user_id < 1)
        {
            SBC::userError('Could not find user in database');
        }

        // Set
        $Member->idAddOne($profile_user_id);
        $this->profile_username = $this->user_row['username'];
        $this->profile_user_id  = $profile_user_id;
    }

    // Get User Row
    final public function getUserRow()
    {
        return $this->user_row;
    }

    // Get Gallery for Entries
    final private function getEntriesGallery(&$db)
    {
        $method = 'UPage->getEntriesGallery()';

        // Initialize
        $Images             = &$this->obj_array['Images'];
        $profile_user_id    = $this->profile_user_id;
        $table_name         = 'u'.$profile_user_id.'c';
        $entry_list         = '';

        if ($profile_user_id < 1)
        {
            SBC::devError('User ID is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe_users');

        // Get entries (type 1)
        $sql = 'SELECT cid
            FROM '.$table_name.'
            WHERE type=1
            ORDER BY cid
            DESC
            LIMIT 20';
        $result = $db->sql_query($sql);
        $rownum = $db->sql_numrows($result);

        // Create entry list
        if ($rownum > 0)
        {
            while ($trow = mysqli_fetch_assoc($result))
            {
                if ($trow['cid'] > 0)
                {
                    $entry_list .= $trow['cid'].' ';
                }
            }
            mysqli_data_seek($result,0);
        }

        // Clean
        $entry_list = SBC::idClean($entry_list);

        // Did we find anything?
        if (empty($entry_list))
        {
            return null;
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get entries
        $sql = 'SELECT id, image_id
            FROM challenge_entries
            WHERE id IN('.$entry_list.')
            ORDER BY id
            DESC';
        $this->entries_result   = $db->sql_query($sql);
        $this->entries_rownum   = $db->sql_numrows($this->entries_result);

        // Add images
        $Images->idAddRows($this->entries_result,'image_id');
    }

    // Get Entries Result
    final public function getEntriesResult()
    {
        return $this->entries_result;
    }

    // Get Entries Rownum
    final public function getEntriesRownum()
    {
        return $this->entries_rownum;
    }
}