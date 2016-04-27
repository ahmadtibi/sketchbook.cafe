<?php
// @author          Jonathan Maltezo (Kameloh)
// @lastUpdated     2016-04-27
namespace SketchbookCafe\TableMailbox;

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\TableCreator\TableCreator as TableCreator;

class TableMailbox
{
    private $mail_id = 0;
    private $hasinfo = 0;

    // Construct
    public function __construct($mail_id)
    {
        $method = 'TableMailbox->__construct()';

        // Set Mailbox ID
        $this->mail_id = isset($mail_id) ? (int) $mail_id : 0;
        if ($this->mail_id < 1)
        {
            SBC::devError('$mail_id is not set',$method);
        }

        // Set hasinfo
        $this->hasinfo = 1;
    }

    // Has info
    final private function hasInfo()
    {
        $method = 'TableMailbox->hasInfo()';

        if ($this->hasinfo != 1)
        {
            SBC::devError('$hasinfo is not set',$method);
        }
    }

    // Check Tables
    final public function checkTables(&$db)
    {
        $method = 'TableMailbox->checkTables()';

        // has info
        $this->hasInfo();

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