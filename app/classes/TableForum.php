<?php
// Creates and manages the main forum table

class TableForum
{
    private $forum_id = 0;
    private $hasinfo = 0;

    // Construct
    public function __construct($forum_id)
    {
        // Set
        $this->forum_id = isset($forum_id) ? (int) $forum_id : 0;
        if ($this->forum_id < 1)
        {
            error('Dev error: $forum_id is not set for TableForum->construct()');
        }

        // Set Has Info
        $this->hasinfo = 1;
    }

    // Has Info
    final private function hasInfo()
    {
        if ($this->hasinfo != 1)
        {
            error('Dev error: $hasinfo is not set for TableForum->hasInfo()');
        }
    }

    // Check Tables
    final public function checkTables(&$db)
    {
        // Has Info
        $this->hasInfo();

        // Class
        sbc_class('TableCreator');

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