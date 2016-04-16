<?php

class UpdateMailbox
{
    private $user_id = 0;

    // Construct
    public function __construct($user_id)
    {
        // Initialize Vars
        $this->user_id = isset($user_id) ? (int) $user_id : 0;

        // Check
        if ($this->user_id < 1)
        {
            error('Dev error: $user_id is not set for UpdateMailbox->construct()');
        }
    }

    // Update Mailbox Timer
    final public function updateTimer(&$db)
    {
        // Initialize Vars
        $time       = time();
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
        if (!$stmt->execute())
        {
            error('Could not execute statement (update user mail timer) for UpdateMailbox->updateTimer()');
        }
        $stmt->close();
    }
}