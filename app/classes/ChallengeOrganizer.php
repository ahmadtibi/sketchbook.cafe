<?php
// @author          Kameloh
// @lastUpdated     2016-05-17
namespace SketchbookCafe\ChallengeOrganizer;

use SketchbookCafe\SBC\SBC as SBC;

class ChallengeOrganizer
{
    private $db;
    private $ip_address = '';
    private $time = 0;
    private $rd = 0;

    public $entry = [];
    private $entry_id_list = '';

    private $verified = [];

    private $challenge_row = [];

    // Construct
    public function __construct(&$db)
    {
        $this->db           = &$db;
        $this->ip_address   = SBC::getIpAddress();
        $this->time         = SBC::getTime();
        $this->rd           = SBC::rd();
    }

    // Get New Entry
    final public function getNewEntry($input)
    {
        $method = 'ChallengeOrganizer->getNewEntry()';

        // Initialize
        $db             = &$this->db;
        $ip_address     = $this->ip_address;
        $rd             = $this->rd;
        $time           = $this->time;

        // Set
        $challenge_id   = SBC::checkNumber($input['challenge_id'],'$challenge_id');
        $comment_id     = SBC::checkNumber($input['comment_id'],'$comment_id');
        $image_id       = SBC::checkNumber($input['image_id'],'$image_id');
        $user_id        = SBC::checkNumber($input['user_id'],'$user_id');
        $difficulty     = SBC::checkNumber($input['challenge_difficulty'],'$challenge_difficulty');

        // Verify Difficulty
        if ($difficulty < 1 || $difficulty > 10)
        {
            SBC::devError('Invalid $difficulty',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Insert new entry
        $sql = 'INSERT INTO challenge_entries
            SET rd=?,
            difficulty=?,
            challenge_id=?,
            comment_id=?,
            image_id=?,
            user_id=?,
            date_created=?,
            ip_created=?,
            isnew=1,
            ispending=1,
            isdeleted=1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('iiiiiiis',$rd,$difficulty,$challenge_id,$comment_id,$image_id,$user_id,$time,$ip_address);
        SBC::statementExecute($stmt,$db,$sql,$method);

        // Get Entry ID
        $sql = 'SELECT id
            FROM challenge_entries
            WHERE rd=?
            AND challenge_id=?
            AND comment_id=?
            AND user_id=?
            AND date_created=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('iiiii',$rd,$challenge_id,$comment_id,$user_id,$time);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Verify
        $entry_id = isset($row['id']) ? (int) $row['id'] : 0;
        if ($entry_id < 1)
        {
            SBC::devError('Could not insert new entry into database',$method);
        }

        // Update Entry as not deleted
        $sql = 'UPDATE challenge_entries
            SET isdeleted=0
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$entry_id);
        SBC::statementExecute($stmt,$db,$sql,$method);

        return $entry_id;
    }

    // Get Pending
    final public function getPending(&$db)
    {
        $method = 'ChallengeOrganizer->getPending()';

        // List?
        $list   = $this->entry_id_list;
        $list   = str_replace(' ',',',trim($list));
        if (empty($list))
        {
            return null;
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get entries
        $sql = 'SELECT id, ispending
            FROM challenge_entries
            WHERE id IN('.$list.')';
        $result = $db->sql_query($sql);
        $rownum = $db->sql_numrows($result);

        if ($rownum > 0)
        {
            while ($trow = mysqli_fetch_assoc($result))
            {
                // Set Array
                $this->entry[$trow['id']] = array(
                    'ispending' => $trow['ispending'],
                );
            }
            mysqli_data_seek($result,0);
        }

        // Return
        return $this->entry;
    }

    // Add Entries based off result
    // use $entry_id_list as list
    final public function idAddEntriesByResult($result,$column)
    {
        $method = 'ChallengeOrganizer->idAddEntriesByResult()';

        // Do we have a result?
        if (!empty($result))
        {
            // Count
            $rownum = mysqli_num_rows($result);
            if ($rownum > 0)
            {
                // Loop
                while ($trow = mysqli_fetch_assoc($result))
                {
                    $id = $trow[$column];
                    if ($id > 0)
                    {
                        $this->entry_id_list .= $id.' ';
                    }
                }
                mysqli_data_seek($result,0);
            }
        }
    }

    // Verify Challenge
    final private function verifyChallenge($challenge_id)
    {
        $method = 'ChallengeOrganizer->verifyChallenge()';

        // Is it already verified?
        if (isset($this->verified[$challenge_id]))
        {
            if ($this->verified[$challenge_id] == 1)
            {
                return null;
            }
        }

        // Initialize
        $db     = &$this->db;
        if ($challenge_id < 1)
        {
            SBC::devError('Challenge ID is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get challenge info
        $sql = 'SELECT id
            FROM challenges
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$challenge_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Verify
        $challenge_id   = isset($row['id']) ? (int) $row['id'] : 0;
        if ($challenge_id < 1)
        {
            SBC::devError('Could not find challenge in database',$method);
        }

        // Set Array
        $this->challenge_row[$challenge_id] = $row;

        // Mark as verified
        $this->verified[$challenge_id] = 1;
    }

    // Generate Thumbnails
    final public function updateLastImages($challenge_id)
    {
        $method = 'ChallengeOrganizer->generateImagesArray()';

        // Verify
        $this->verifyChallenge($challenge_id);

        // Initialize
        $db             = &$this->db;
        $table_name     = 'fc'.$challenge_id.'l';
        $entry_list     = '';
        $images_array   = '';

        // Switch
        $db->sql_switch('sketchbookcafe_challenges');

        // Get last 4 entries
        $sql = 'SELECT entry_id
            FROM '.$table_name.'
            WHERE ispending=0
            ORDER BY id
            DESC
            LIMIT 4';
        $result = $db->sql_query($sql);
        $rownum = $db->sql_numrows($result);

        // Create Entry List
        if ($rownum > 0)
        {
            // Loop
            while ($trow = mysqli_fetch_assoc($result))
            {
                if ($trow['entry_id'] > 0)
                {
                    $entry_list .= $trow['entry_id'].' ';
                }
            }
            $db->sql_freeresult($result);
        }

        // Clean List
        $entry_list = SBC::idClean($entry_list);

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Entries
        if (!empty($entry_list))
        {
            $sql = 'SELECT image_id
                FROM challenge_entries
                WHERE id IN('.$entry_list.')';
            $result = $db->sql_query($sql);
            $rownum = $db->sql_numrows($result);

            if ($rownum > 0)
            {
                while ($trow = mysqli_fetch_assoc($result))
                {
                    if ($trow['image_id'] > 0)
                    {
                        $images_array .= $trow['image_id'].' ';
                    }
                }
                $db->sql_freeresult($result);
            }
        }

        // Clean
        $images_array   = SBC::idClean($images_array);

        // Update Challenge
        $sql = 'UPDATE challenges
            SET images_array=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('si',$images_array,$challenge_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }

    // Update Total Entries
    final public function updateTotalEntries($challenge_id)
    {
        $method = 'ChallengeOrganizer->updateTotalEntries()';

        // Verify
        $this->verifyChallenge($challenge_id);

        // Initialize
        $db         = &$this->db;
        $table_name = 'fc'.$challenge_id.'l';

        // Switch
        $db->sql_switch('sketchbookcafe_challenges');

        // Count total
        $sql = 'SELECT COUNT(*)
            FROM '.$table_name.'
            WHERE ispending=0';
        $result = $db->sql_query($sql);
        $row    = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);

        // Total?
        $total  = isset($row[0]) ? (int) $row[0] : 0;

        // Count Pending
        $sql = 'SELECT COUNT(*)
            FROM '.$table_name.'
            WHERE ispending=1';
        $result = $db->sql_query($sql);
        $row    = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);

        // Pending Total
        $total_pending = isset($row[0]) ? (int) $row[0] : 0;

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Update totals for challenge
        $sql = 'UPDATE challenges
            SET total_entries=?,
            total_pending=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('iii',$total,$total_pending,$challenge_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }

    // Get Challenge Row
    final public function getChallengeRow($challenge_id)
    {
        $method = 'ChallengeOrganizer->getChallengeRow()';
    }

    // Update Difficulty
    final public function updateDifficulty($challenge_id)
    {
        $method = 'ChallengeOrganizer->updateDifficulty()';

        // Verify
        $this->verifyChallenge($challenge_id);

        // Initialize
        $db                 = &$this->db;
        $table_name         = 'fc'.$challenge_id.'l';
        $difficulty_votes   = 0;
        $difficulty_total   = 0;
        $difficulty_max     = 0;

        // Switch
        $db->sql_switch('sketchbookcafe_challenges');

        // Get all difficulty ratings
        $sql = 'SELECT difficulty
            FROM '.$table_name.'
            WHERE ispending=0';
        $result = $db->sql_query($sql);
        $rownum = $db->sql_numrows($result);

        if ($rownum > 0)
        {
            $difficulty_votes   = $rownum;
            $difficulty_max     = $rownum * 10; // just multiply it - simple

            while ($trow = mysqli_fetch_assoc($result))
            {
                $difficulty_total += $trow['difficulty'];
            }
            $db->sql_freeresult($result);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Update Challenge
        $sql = 'UPDATE challenges
            SET difficulty_votes=?,
            difficulty_total=?,
            difficulty_max=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('iiii',$difficulty_votes,$difficulty_total,$difficulty_max,$challenge_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }
}