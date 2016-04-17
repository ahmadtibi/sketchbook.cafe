<?php

class TableMailbox
{
    private $mail_id = 0;
    private $hasinfo = 0;

    // Construct
    public function __construct($mail_id)
    {
        // Set Mailbox ID
        $this->mail_id = isset($mail_id) ? (int) $mail_id : 0;
        if ($this->mail_id < 1)
        {
            error('Dev error: $mail_id is not set for TableMailbox->construct()');
        }

        // Set hasinfo
        $this->hasinfo = 1;
    }

    // Has info
    final private function hasInfo()
    {
        if ($this->hasinfo != 1)
        {
            error('Dev error: $hasinfo is not set for TableMailbox->hasInfo()');
        }
    }

    // Check Tables
    final public function checkTables(&$db)
    {
        // has info
        $this->hasInfo();

        // Class
        sbc_class('TableCreator');

        // Tables
        $tablename  = 'm'.$this->mail_id.'x';
        $database   = 'sketchbookcafe_mailbox';
        $columns    = array
        (
            'id'    => 'INT NOT NULL AUTO_INCREMENT',
            'cid'   => 'INT DEFAULT 0 NOT NULL',
        );
        $TableCreator   = new TableCreator($tablename,$database,$columns);
        $TableCreator->createTable($db);

        // Unset Vars
        unset($tablename);
        unset($database);
        unset($columns);
        unset($TableCreator);
    }
}