<?php
// @author          Jonathan Maltezo (Kameloh)
// @lastUpdated     2016-04-27
namespace SketchbookCafe\UpdateMailbox;

use SketchbookCafe\SBC\SBC as SBC;

class UpdateMailbox
{
    private $user_id = 0;

    // Construct
    public function __construct($user_id)
    {
        $method = 'UpdateMailbox->__construct()';

        // Initialize Vars
        $this->user_id = isset($user_id) ? (int) $user_id : 0;

        // Check
        if ($this->user_id < 1)
        {
            SBC::devError('$user_id is not set',$method);
        }
    }

    // Update Mailbox Timer
    final public function updateTimer(&$db)
    {
        $method = 'UpdateMailbox->updateTimer()';

        // Initialize Vars
        $time       = SBC::getTime();
        $user_id    = $this->user_id;

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Update mailbox timer
        $sql = 'UPDATE users
            SET mailbox_update=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ii',$time,$user_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }
}