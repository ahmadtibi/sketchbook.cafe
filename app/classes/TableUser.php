<?php
// @author          Jonathan Maltezo
// @lastUpdated     2016-04-27
namespace SketchbookCafe\TableUser;

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\TableCreator\TableCreator as TableCreator;

class TableUser {
    private $user_id = 0;
    private $hasinfo = 0;

    // Construct
    public function __construct($user_id)
    {
        $method = 'TableUser->__construct()';

        // Set User ID
        $this->user_id = isset($user_id) ? (int) $user_id : 0;
        if ($this->user_id < 1)
        {
            SBC::devError('$user_id is not set',$method);
        }

        // Set hasinfo
        $this->hasinfo = 1;
    }

    // Has Info
    final public function hasInfo()
    {
        $method = 'TableUser->hasInfo()';

        if ($this->hasinfo != 1)
        {
            SBC::devError('$hasinfo is not set',$method);
        }
    }

    // Check Tables
    final public function checkTables(&$db)
    {
        $method = 'TableUser->checkTables()';

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

        // Unset tables
        unset($tablename);
        unset($database);
        unset($columns);
        unset($TableCreator);

        // Table: Viewed Thread for Forums
        $tablename  = 'u'.$this->user_id.'vt';
        $database   = 'sketchbookcafe_users';
        $columns    = array
        (
            'id'    => 'INT NOT NULL AUTO_INCREMENT', 
            'cid'   => 'INT DEFAULT 0 NOT NULL',
            'pda'   => 'BIGINT DEFAULT 0 NOT NULL',
            'lda'   => 'BIGINT DEFAULT 0 NOT NULL',
        );
        $TableCreator = new TableCreator($tablename,$database,$columns);
        $TableCreator->createTable($db);
    }
}