<?php
// @author          Kameloh
// @lastUpdated     2016-05-05
// 
// $Challenge = new Challenge($db);
// $Challenge->setChallengeId($challenge_id);
// $Challenge->process();
// $Challenge->getPending();
namespace SketchbookCafe\Challenge;

use SketchbookCafe\SBC\SBC as SBC;

class Challenge
{
    private $challenge_id = 0;
    private $category_id = 0;
    private $owner_user_id = 0;

    // Results and Rownums
    public $challenge_row = [];
    public $challenge_entries_result = [];
    public $challenge_entries_rownum = [];

    // Generated
    private $isverified = 0;

    // Database
    private $db;

    // Construct
    public function __construct(&$db)
    {
        $this->db = &$db;
    }

    // Set Challenge ID
    final public function setChallengeId($challenge_id)
    {
        $method = 'Challenge->setChallengeId()';

        $this->challenge_id = isset($challenge_id) ? (int) $challenge_id : 0;
        if ($this->challenge_id < 1)
        {
            SBC::devError('$challenge_id is not set',$method);
        }
    }

    // Process Challenge
    final public function process()
    {
        $method = 'Challenge->process()';
        $db     = &$this->db;

        $challenge_id   = $this->challenge_id;
        if ($challenge_id < 1)
        {
            SBC::devError('$challenge_id is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Challenge Information
        $sql = 'SELECT id, category_id, thread_id, owner_user_id, date_created, date_updated, 
            points, name, description, requirements, total_entries, total_pending,
            difficulty_votes, difficulty_total, difficulty_max, ispending, isdeleted
            FROM challenges
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$challenge_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Verify Challenge
        $challenge_id = isset($row['id']) ? (int) $row['id'] : 0;
        if ($challenge_id < 1)
        {
            SBC::devError('Could not find challenge in database',$method);
        }

        // Set vars
        $this->category_id      = $row['category_id'];
        $this->owner_user_id    = $row['owner_user_id'];

        // Set Array
        $this->challenge_row    = $row;

        // Set as verified
        $this->isverified       = 1;
    }

    // Verified
    final private function isVerified()
    {
        $method = 'Challenge->isVerified()';

        if ($this->isverified != 1)
        {
            SBC::devError('Challenge is not verified',$method);
        }
    }

    // Get Pending
    final public function getPending()
    {
        $method = 'Challenge->getPending()';
        $db     = &$this->db;

        // Verified
        $this->isVerified();

        $challenge_id   = $this->challenge_id;

        // Switch
        $db->sql_switch('sketchbookcafe_challenges');

        // Get entries (non statement)
        $table_name = 'fc'.$challenge_id.'l';
        $sql = 'SELECT cid
            FROM '.$table_name.'
            WHERE ispending=1
            ORDER BY cid
            DESC';
        $result = $db->sql_query($sql);
        $rownum = $db->sql_numrows($result);

        // Did we find any results?
        $id_list = '';
        if ($rownum > 0)
        {
            // Loop
            while ($trow = mysqli_fetch_assoc($result))
            {
                if ($trow['cid'] > 0)
                {
                    $id_list .= $trow['cid'].' ';
                }
            }
            mysqli_data_seek($result,0);
        }

        // Clean
        $id_list = str_replace(' ',',',trim($id_list));

        // Empty?
        if (!empty($id_list))
        {
            // Get Comments by List
            $this->getComments($id_list);

            // Get Entries by List
            // $this->getEntries('pending',$id_list);
        }
    }

    // Get Comments by List
    final private function getComments($id_list)
    {
        $method = 'Challenge->getComments()';
        $db     = &$this->db;

        // Check ID List
        $id_list    = SBC::idClean($id_list);
        if (empty($id_list))
        {
            return null;
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Comments
        $sql = 'SELECT id, image_id, entry_id
            FROM sbc_comments
            WHERE id IN('.$id_list.')';
        $result = $db->sql_query($sql);
        $rownum = $db->sql_numrows($result);

        // 
    }

    // Get Entries by List
    final private function getEntries($type,$id_list)
    {
        $method = 'Challenge->getEntries()';
        $db     = &$this->db;

        // Type must be set
        if ($type != 'pending')
        {
            if ($type != 'approved')
            {
                SBC::devError('$type is not valid',$method);
            }
        }

        // Check ID List
        $id_list    = SBC::idClean($id_list);
        if (empty($id_list))
        {
            return null;
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Entries
        $sql = 'SELECT id, challenge_id, comment_id, image_id, user_id, date_created, isnew, ispending, isdeleted
            FROM challenge_entries
            WHERE id IN('.$id_list.')
            ORDER BY id 
            DESC';
        $this->challenge_entries_result[$type] = $db->sql_query($sql);
        $this->challenge_entries_rownum[$type] = $db->sql_numrows($this->challenge_entries_result[$type]);

    }

}