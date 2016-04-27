<?php
// @author          Jonathan Maltezo (Kameloh)
// @lastUpdated     2016-04-27
// Creates and manages the main forum table
namespace SketchbookCafe\TableForum;

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\TableCreator\TableCreator as TableCreator;

class TableForum
{
    private $forum_id = 0;
    private $hasinfo = 0;

    // Construct
    public function __construct($forum_id)
    {
        $method = 'TableForum->__construct()';

        // Set
        $this->forum_id = isset($forum_id) ? (int) $forum_id : 0;
        if ($this->forum_id < 1)
        {
            SBC::devError('$forum_id is not set',$method);
        }

        // Set Has Info
        $this->hasinfo = 1;
    }

    // Has Info
    final private function hasInfo()
    {
        $method = 'TableForum->hasInfo()';

        if ($this->hasinfo != 1)
        {
            SBC::devError('$hasinfo is not set',$method);
        }
    }

    // Check Tables
    final public function checkTables(&$db)
    {
        $method = 'TableForum->checkTables()';

        // Has Info
        $this->hasInfo();

        // Tables
        $tablename  = 'forum'.$this->forum_id.'x';
        $database   = 'sketchbookcafe_forums';
        $columns    = array
        (
            'id'                => 'INT NOT NULL AUTO_INCREMENT',
            'thread_id'         => 'INT DEFAULT 0 NOT NULL',
            'date_created'      => 'BIGINT DEFAULT 0 NOT NULL',
            'date_updated'      => 'BIGINT DEFAULT 0 NOT NULL',
            'date_bumped'       => 'BIGINT DEFAULT 0 NOT NULL',
            'user_id'           => 'INT DEFAULT 0 NOT NULL',
            'last_user_id'      => 'INT DEFAULT 0 NOT NULL',
            'last_comment_id'   => 'INT DEFAULT 0 NOT NULL',
            'total_views'       => 'INT DEFAULT 0 NOT NULL',
            'total_comments'    => 'INT DEFAULT 0 NOT NULL',
            'total_karma'       => 'INT DEFAULT 0 NOT NULL',
            'total_likes'       => 'INT DEFAULT 0 NOT NULL',
            'is_poll'           => 'TINYINT(1) DEFAULT 0 NOT NULL',
            'is_locked'         => 'TINYINT(1) DEFAULT 0 NOT NULL',
            'is_sticky'         => 'TINYINT(1) DEFAULT 0 NOT NULL',
        );
        $TableCreator   = new TableCreator($tablename,$database,$columns);
        $TableCreator->createTable($db);
    }
}