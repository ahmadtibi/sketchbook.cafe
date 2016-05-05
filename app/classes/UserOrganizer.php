<?php
// @author          Kameloh
// @lastUpdated     2016-05-02
// Organizes user statistics
namespace SketchbookCafe\UserOrganizer;

use SketchbookCafe\SBC\SBC as SBC;

class UserOrganizer
{
    private $db;
    private $user_id = 0;
    private $verified = [];

    // Construct
    public function __construct(&$db)
    {
        $this->db = &$db;
    }

    // Verify User
    final private function verifyUser($user_id)
    {
        $method = 'UserOrganizer->verifyUser()';

        $db     = &$this->db;

        // Already verified?
        if (isset($this->verified[$user_id]))
        {
            if ($this->verified[$user_id] == 1)
            {
                return null;
            }
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Verify if the user exists
        $sql = 'SELECT id
            FROM users
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$user_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Verify
        $temp_user_id    = isset($row['id']) ? (int) $row['id'] : 0;
        if ($temp_user_id < 1)
        {
            SBC::devError('Could not find user_id('.$user_id.') in database',$method);
        }

        // Set as verified
        $this->verified[$user_id] = 1;
    }

    // Total Forum Subscriptions
    final public function totalForumSubscriptions($user_id)
    {
        $method = 'UserOrganizer->totalForumSubscriptions()';

        $db     = &$this->db;

        // Verify User
        $this->verifyUser($user_id);

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Do they have a subscription table?
        $sql = 'SELECT table_forum_subscriptions
            FROM users
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$user_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Set
        $hastable   = $row['table_forum_subscriptions'];

        // If not, return null
        if ($hastable < 1)
        {
            return null;
        }

        // Switch
        $db->sql_switch('sketchbookcafe_users');

        // Table Name
        $table_name = 'u'.$user_id.'fs';

        // Count
        $sql = 'SELECT COUNT(*)
            FROM '.$table_name;
        $result = $db->sql_query($sql);
        $row    = $db->sql_fetchrow($result);

        // Total
        $total  = isset($row[0]) ? (int) $row[0] : 0;

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Update total subscribed threads
        $sql = 'UPDATE users
            SET total_thread_subscriptions=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ii',$total,$user_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }
}