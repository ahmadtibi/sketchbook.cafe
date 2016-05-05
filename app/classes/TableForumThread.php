<?php
// @author          Kameloh
// @lastUpdated     2016-04-27
namespace SketchbookCafe\TableForumThread;

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\TableCreator\TableCreator as TableCreator;

class TableForumThread
{
    private $thread_id = 0;
    private $hasinfo = 0;

    // Construct
    public function __construct($thread_id)
    {
        $method = 'TableForumThread->__construct()';

        // Set Thread ID
        $this->thread_id = isset($thread_id) ? (int) $thread_id : 0;
        if ($this->thread_id < 1)
        {
            SBC::devError('$thread_id is not set',$method);
        }

        // Set Has Info
        $this->hasinfo = 1;
    }

    // Has Info
    final private function hasInfo()
    {
        $method = 'TableForumThread->hasInfo()';

        if ($this->hasinfo != 1)
        {
            SBC::devError('$hasinfo is not set',$method);
        }
    }

    // Check Tables
    final public function checkTables(&$db)
    {
        $method = 'TableForumThread->checkTables()';

        // Has Info
        $this->hasinfo();

        // Comment Table
        $tablename  = 't'.$this->thread_id.'d';
        $database   = 'sketchbookcafe_forums';
        $columns    = array
        (
            'id'    => 'INT NOT NULL AUTO_INCREMENT',
            'cid'   => 'INT DEFAULT 0 NOT NULL',
            'uid'   => 'INT DEFAULT 0 NOT NULL',
        );
        $TableCreator   = new TableCreator($tablename,$database,$columns);
        $TableCreator->createTable($db);

        // Unset
        unset($tablename);
        unset($database);
        unset($columns);
        unset($TableCreator);
    }
}