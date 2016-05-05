<?php
// @author          Kameloh
// @lastUpdated     2016-05-02
namespace SketchbookCafe\TableUserForumSub;

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\TableCreator\TableCreator as TableCreator;

class TableUserForumSub
{
    private $user_id = 0;
    private $hasinfo = 0;

    // Construct
    public function __construct($user_id)
    {
        $method = 'TableUserForumSub->__construct()';

        // Set User Id
        $this->user_id = isset($user_id) ? (int) $user_id : 0;
        if ($this->user_id < 1)
        {
            SBC::devError('$user_id is not set',$method);
        }

        // Set has info
        $this->hasinfo = 1;
    }

    // Has Info
    final private function hasInfo()
    {
        $method = 'TableUserForumSub->hasInfo()';

        if ($this->hasinfo != 1)
        {
            SBC::devError('$hasinfo is not set',$method);
        }
    }

    // Check Tables
    final public function checkTables(&$db)
    {
        $method = 'TableUserForumSub->checkTables()';

        // Table: User Forum Subscription
        $tablename  = 'u'.$this->user_id.'fs';
        $database   = 'sketchbookcafe_users';
        $columns    = array
        (
            'id'    => 'INT NOT NULL AUTO_INCREMENT',
            'tid'   => 'INT DEFAULT 0 NOT NULL',
            'pda'   => 'BIGINT DEFAULT 0 NOT NULL',
            'lda'   => 'BIGINT DEFAULT 0 NOT NULL',
        );
        $TableCreator   = new TableCreator($tablename,$database,$columns);
        $TableCreator->createTable($db);

        // Unset Tables
        unset($tablename);
        unset($database);
        unset($columns);
        unset($TableCreator);
    }
}