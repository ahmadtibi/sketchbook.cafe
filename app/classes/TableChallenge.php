<?php
// @author          Kameloh
// @lastUpdated     2016-05-09
namespace SketchbookCafe\TableChallenge;

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\TableCreator\TableCreator as TableCreator;

class TableChallenge
{
    private $challenge_id = 0;
    private $hasinfo = 0;

    // Construct
    public function __construct($challenge_id)
    {
        $method = 'TableChallenge->__construct()';

        // Set
        $this->challenge_id = isset($challenge_id) ? (int) $challenge_id : 0;
        if ($this->challenge_id < 1)
        {
            SBC::devError('$challenge_id is not set',$method);
        }

        // Has info
        $this->hasinfo = 1;
    }

    // Has Info
    final private function hasInfo()
    {
        $method = 'TableChallenge->hasInfo()';

        if ($this->hasinfo != 1)
        {
            SBC::devError('$hasinfo is not set',$method);
        }
    }

    // Check Tables
    final public function checkTables(&$db)
    {
        $method = 'TableChallenge->checkTables()';

        // Has Info
        $this->hasInfo();

        // Tables
        $tablename  = 'fc'.$this->challenge_id.'l';
        $database   = 'sketchbookcafe_challenges';
        $columns    = array
        (
            'id'            => 'INT NOT NULL AUTO_INCREMENT',
            'cid'           => 'INT DEFAULT 0 NOT NULL',
            'entry_id'      => 'INT DEFAULT 0 NOT NULL',
            'uid'           => 'INT DEFAULT 0 NOT NULL',
            'difficulty'    => 'TINYINT(2) DEFAULT 0 NOT NULL',
            'ispending'     => 'TINYINT(1) DEFAULT 0 NOT NULL',
        );
        $TableCreator   = new TableCreator($tablename,$database,$columns);
        $TableCreator->createTable($db);
    }
}