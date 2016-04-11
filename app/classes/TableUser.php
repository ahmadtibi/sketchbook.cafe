<?php
// Table: Users
// Contains all table information for users
class TableUser {
    private $user_id = 0;
    private $hasinfo = 0;

    // Construct
    public function __construct($user_id)
    {
        // Set User ID
        $this->user_id = isset($user_id) ? (int) $user_id : 0;
        if ($this->user_id < 1)
        {
            error('Dev error: $user_id is not set for TableUser->construct()');
        }

        // Set hasinfo
        $this->hasinfo = 1;
    }

    // Has Info
    final public function hasInfo()
    {
        if ($this->hasinfo != 1)
        {
            error('Dev error: $hasinfo is not set for TableUser->hasInfo()');
        }
    }

    // Check Tables
    final public function checkTables(&$db)
    {
        // Class
        sbc_class('TableCreator');

        // Table: User Content
        $tablename  = 'u'.$this->user_id.'c';
        $database   = 'sketchbookcafe_users';
        $columns    = array
        (
            'id'    => 'INT NOT NULL AUTO_INCREMENT', 
            'type'  => 'TINYINT(2) DEFAULT 0 NOT NULL',
            'type2' => 'TINYINT(2) DEFAULT 0 NOT NULL',
            'cid'   => 'INT DEFAULT 0 NOT NULL',
            'fid'   => 'INT DEFAULT 0 NOT NULL',
        );
        $TableCreator = new TableCreator($tablename,$database,$columns);
        $TableCreator->createTable($db);

        // Unset tables
        unset($tablename);
        unset($database);
        unset($columns);
        unset($TableCreator);

        // Table: Mailbox
        $tablename  = 'u'.$this->user_id.'m';
        $database   = 'sketchbookcafe_users';
        $columns    = array
        (
            'id'            => 'INT NOT NULL AUTO_INCREMENT', 
            'cid'           => 'INT DEFAULT 0 NOT NULL',
            'fid'           => 'INT DEFAULT 0 NOT NULL',
            'lastupdate'    => 'BIGINT DEFAULT 0 NOT NULL',
            'replied'       => 'TINYINT(1) DEFAULT 0 NOT NULL',
            'isnew'         => 'TINYINT(1) DEFAULT 0 NOT NULL',
        );
        $TableCreator = new TableCreator($tablename,$database,$columns);
        $TableCreator->createTable($db);

        // Unset tables
        unset($tablename);
        unset($database);
        unset($columns);
        unset($TableCreator);

        // Table: Notifications
        $tablename  = 'u'.$this->user_id.'n';
        $database   = 'sketchbookcafe_users';
        $columns    = array
        (
            'id'    => 'INT NOT NULL AUTO_INCREMENT', 
            'type'  => 'TINYINT(2) DEFAULT 0 NOT NULL',
            'type2' => 'TINYINT(2) DEFAULT 0 NOT NULL',
            'cid'   => 'INT DEFAULT 0 NOT NULL',
        );
        $TableCreator = new TableCreator($tablename,$database,$columns);
        $TableCreator->createTable($db);
    }
}