<?php
// @author          Kameloh
// @lastUpdated     2016-04-27
// Stats Organizer
namespace SketchbookCafe\StatsOrganizer;

use SketchbookCafe\SBC\SBC as SBC;

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
        $method = 'StatsOrganizer->userForumPostAdd()';

        // Initialize Objects and Vars
        $db  = $this->db;

        // Check
        if ($user_id < 1)
        {
            SBC::devError('$user_id is not set',$method);
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
        SBC::statementExecute($stmt,$db,$sql,$method);
    }
}