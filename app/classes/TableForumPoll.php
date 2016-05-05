<?php
// @author          Kameloh
// @lastUpdated     2016-05-01
namespace SketchbookCafe\TableForumPoll;

use SketchbookCafe\SBC\SBC as SBC;
use Sketchbookcafe\TableCreator\TableCreator as TableCreator;

class TableForumPoll
{
    private $poll_id = 0;
    private $hasinfo = 0;

    // Construct
    public function __construct($poll_id)
    {
        $method = 'TableForumPoll->__construct()';

        // Set Poll ID
        $this->poll_id = isset($poll_id) ? (int) $poll_id : 0;
        if ($this->poll_id < 1)
        {
            SBC::devError('$poll_id is not set',$method);
        }

        // Set has info
        $this->hasinfo = 1;
    }

    // Has Info
    final private function hasInfo()
    {
        $method = 'TableForumPoll->hasInfo()';

        if ($this->hasinfo != 1)
        {
            SBC::devError('$hasinfo is not set',$method);
        }
    }

    // Check Tables
    final public function checkTables(&$db)
    {
        $method = 'TableForumPoll->checkTables()';

        // Has Info
        $this->hasInfo();

        // Poll Table
        $poll_id    = SBC::checkNumber($this->poll_id,'$this->poll_id');
        $tablename  = 'p'.$poll_id.'l';
        $database   = 'sketchbookcafe_forums';
        $columns    = array
        (
            'id'            => 'INT NOT NULL AUTO_INCREMENT',
            'user_id'       => 'INT DEFAULT 0 NOT NULL',
            'vote_id'       => 'INT DEFAULT 0 NOT NULL',
            'poll_option'   => 'TINYINT(2) DEFAULT 0 NOT NULL',
        );

        // Table Creator
        $TableCreator   = new TableCreator($tablename,$database,$columns);
        $TableCreator->createTable($db);

        // Unset
        unset($tablename);
        unset($database);
        unset($columns);
        unset($TableCreator);
    }
}