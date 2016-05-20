<?php
// @author          Kameloh
// @lastUpdated     2016-05-19
namespace SketchbookCafe\MailboxOrganizer;

use SketchbookCafe\SBC\SBC as SBC;

class MailboxOrganizer
{
    private $time = 0;

    private $db;

    public function __construct(&$db)
    {
        $this->db   = &$db;
        $this->time = SBC::getTime();
    }

    // Update Timer
    final public function updateTimer($user_id)
    {
        $method = 'MailboxOrganizer->updateTimer()';

        if ($user_id < 1)
        {
            SBC::devError('User ID is not set',$method);
        }

        // Initialize
        $db     = &$this->db;
        $time   = $this->time;

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Update Mailbox Timer
        $sql = 'UPDATE users
            SET mailbox_update=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ii',$time,$user_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }

}