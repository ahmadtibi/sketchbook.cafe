<?php
// Count Mail

class CountMail
{
    private $user_id = 0;

    // Construct
    public function __construct($user_id)
    {
        // Set User ID
        $this->user_id  = isset($user_id) ? (int) $user_id : 0;
        if ($this->user_id < 1)
        {
            error('Dev error: $user_id is not set for CountMail->construct()');
        }
    }

    // Process Count
    final public function process(&$db)
    {
        // Initialize Vars
        $user_id    = $this->user_id;
        if ($user_id < 1)
        {
            error('Dev error: $user_id is not set for CountMail->process()');
        }

        // Switch
        $db->sql_switch('sketchbookcafe_users');

        // Set Table
        $table_name = 'u'.$user_id.'m';

        // Count Total Mail (no statements)
        $sql = 'SELECT COUNT(*)
            FROM '.$table_name;
        $result = $db->sql_query($sql);
        $row    = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);

        // Total
        $total  = isset ($row[0]) ? (int) $row[0] : 0;

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Update User
        $sql = 'UPDATE users
            SET mail_total=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ii',$total,$user_id);
        if (!$stmt->execute())
        {
            error('Could not execute statement (update user) for CountMail->process()');
        }
        $stmt->close();
    }
}