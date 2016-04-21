<?php
// Stats Organizer

class StatsOrganizer
{
    private $db;

    // Construct
    public function __construct(&$db)
    {
        // Set
        $this->db = &$db;
    }

    // User: Add Forum Posts
    final public function userForumPostAdd($user_id)
    {
        // Initialize Objects and Vars
        $db     = $this->db;
        $method = 'StatsOrganizer->userForumAddPost()';

        // Check
        if ($user_id < 1)
        {
            error('Dev error: $user_id is not set for '.$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Update User
        $sql = 'UPDATE users
            SET total_posts=(total_posts + 1)
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$user_id);
        if (!$stmt->execute())
        {
            statement_error('update user posts',$method);
        }
        $stmt->close();
    }
}