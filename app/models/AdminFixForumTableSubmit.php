<?php
// @author          Kameloh
// @lastUpdated     2016-04-27

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\TableForum\TableForum as TableForum;

class AdminFixForumTableSubmit
{
    private $forum_id = 0;
    private $forum_type = 0; // 1 is category, 2 is forum

    // Construct
    public function __construct(&$obj_array)
    {
        $method = 'AdminFixForumTableSubmit->__construct()';

        // Initialize Objects
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // Forum ID
        $this->forum_id = isset($_POST['forum_id']) ? (int) $_POST['forum_id'] : 0;
        if ($this->forum_id < 1)
        {
            SBC::devError('$forum_id is not set',$method);
        }

        // Open Connection
        $db->open();

        // Admin Required + Process Data
        $User->admin($db);
        $User->requireAdminFlag('fix_forum_table');

        // Get Forum Information
        $this->getForumInfo($db);

        // Fix Tables
        $this->checkTable($db);

        // Close Connection
        $db->close();

        // Header
        header('Location: https://www.sketchbook.cafe/admin/fix_forum_table/1/');
        exit;
    }

    // Get Forum Information
    final private function getForumInfo(&$db)
    {
        $method = 'AdminFixForumTableSubmit->getForumInfo()';

        // Initialize Vars
        $forum_id   = $this->forum_id;

        // Check
        if ($forum_id < 1)
        {
            SBC::devError('$forum_id is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Forum Information
        $sql = 'SELECT id, iscategory, isforum
            FROM forums
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$forum_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Check
        $forum_id   = isset($row['id']) ? (int) $row['id'] : 0;
        if ($forum_id < 1)
        {
            SBC::userError('Could not find forum in database');
        }

        // Set Type
        if ($row['iscategory'] == 1)
        {
            $this->type = 1;
        } 
        else if ($row['isforum'] == 1)
        {
            $this->type = 2;
        }
        else
        {
            SBC::devError('iscategory or isforum is not set',$method);
        }
    }

    // Check Table
    final private function checkTable(&$db)
    {
        $method = 'AdminFixForumTableSubmit->checkTable()';

        // Initiailize Vars
        $forum_id   = $this->forum_id;
        $type       = $this->type;

        // Check
        if ($forum_id < 1 || $type < 1)
        {
            SBC::devError('$forum_id:'.$forum_id.', $type:'.$type,$method);
        }

        // Category?
        if ($type == 1)
        {
            // Categories don't have tables! But they might in the future so...
            return null;
        }
        else if ($type == 2)
        {
            // Forum Table
            $ForumTable = new TableForum($forum_id);
            $ForumTable->checkTables($db);
        }
    }
}