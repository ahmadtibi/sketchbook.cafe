<?php

class TableForumThread
{
    private $thread_id = 0;
    private $hasinfo = 0;

    // Construct
    public function __construct($thread_id)
    {
        // Set Thread ID
        $this->thread_id = isset($thread_id) ? (int) $thread_id : 0;
        if ($this->thread_id < 1)
        {
            error('Dev error: $thread_id is not set for TableForumThread->construct()');
        }

        // Set Has Info
        $this->hasinfo = 1;
    }

    // Has Info
    final private function hasInfo()
    {
        if ($this->hasinfo != 1)
        {
            error('Dev error: $hasinfo is not set for TableForumThread->hasInfo()');
        }
    }

    // Check Tables
    final public function checkTables(&$db)
    {
        // Has Info
        $this->hasinfo();

        // Class
        sbc_class('TableCreator');

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