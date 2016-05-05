<?php
// @author          Kameloh
// @lastUpdated     2016-05-03
namespace SketchbookCafe\TableChallengeCategory;

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\TableCreator\TableCreator as TableCreator;

class TableChallengeCategory
{
    private $category_id = 0;
    private $hasinfo = 0;

    // Construct
    public function __construct($category_id)
    {
        $method = 'TableChallengeCategory->__construct()';

        // Set
        $this->category_id = isset($category_id) ? (int) $category_id : 0;
        if ($this->category_id < 1)
        {
            SBC::devError('$category_id is not set',$method);
        }

        // Has info
        $this->hasinfo = 1;
    }

    // Has Info
    final private function hasInfo()
    {
        $method = 'TableChallengeCategory->hasInfo()';

        if ($this->hasinfo != 1)
        {
            SBC::devError('$hasinfo is not set',$method);
        }
    }

    // Check Tables
    final public function checkTables(&$db)
    {
        $method = 'TableChallengeCategory->checkTables()';

        // Has Info
        $this->hasInfo();

        // Tables
        $tablename  = 'c'.$this->category_id.'l';
        $database   = 'sketchbookcafe_challenges';
        $columns    = array
        (
            'id'                => 'INT NOT NULL AUTO_INCREMENT',
            'cid'               => 'INT DEFAULT 0 NOT NULL',
            'date_created'      => 'BIGINT DEFAULT 0 NOT NULL',
            'date_updated'      => 'BIGINT DEFAULT 0 NOT NULL',
            'total_comments'    => 'INT DEFAULT 0 NOT NULL',
            'total_likes'       => 'INT DEFAULT 0 NOT NULL',
            'total_points'      => 'INT DEFAULT 0 NOT NULL',
        );
        $TableCreator   = new TableCreator($tablename,$database,$columns);
        $TableCreator->createTable($db);
    }
}