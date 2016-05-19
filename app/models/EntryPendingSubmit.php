<?php
// @author          Kameloh
// @lastUpdated     2016-05-16

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\ChallengeOrganizer\ChallengeOrganizer as ChallengeOrganizer;
use SketchbookCafe\UserContentOrganizer\UserContentOrganizer as UserContentOrganizer;
use SketchbookCafe\PointsOrganizer\PointsOrganizer as PointsOrganizer;

class EntryPendingSubmit
{
    private $user_id = 0;
    private $entry_id = 0;
    private $challenge_id = 0;
    private $comment_id = 0;

    public function __construct(&$obj_array)
    {
        $method = 'EntryPendingSubmit->__construct()';

        // Initialize
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // Entry ID
        $this->entry_id = isset($_POST['entry_id']) ? (int) $_POST['entry_id'] : 0;
        if ($this->entry_id < 1)
        {
            SBC::devError('Entry ID is not set',$method);
        }

        // Action (1 approve, 2 delete)
        $action = isset($_POST['action']) ? (int) $_POST['action'] : 0;
        if ($action < 1 || $action > 2)
        {
            SBC::userError('Invalid action');
        }

        // Confirm
        $confirm    = isset($_POST['confirm']) ? (int) $_POST['confirm'] : 0;
        if ($confirm != 1)
        {
            SBC::userError('You must confirm action to continue');
        }

        // Open Connection
        $db->open();

        // Admin Required
        $User->admin($db);
        $User->requireAdminFlag('approve_entries');

        // Get Entry Info
        $this->getEntry($db);

        // Approve or delete?
        if ($action == 1)
        {
            $this->approveEntry($db);

            // Add
            $UserContentOrganizer = new UserContentOrganizer($db);
            $UserContentOrganizer->addContentEntry($this->user_id,$this->entry_id);
            $UserContentOrganizer->countContentEntry($this->user_id);

            // Points
            $PointsOrganizer = new PointsOrganizer($db);
            $PointsOrganizer->addPointsByEntry($this->entry_id);
        }
        else
        {
            $this->deleteEntry($db);
        }

        // Close Connection
        $db->close();

        // Header
        header('Location: https://www.sketchbook.cafe/challenges/'.$this->challenge_id.'/2/0/');
        exit;
    }

    // Get Entry
    final private function getEntry(&$db)
    {
        $method = 'EntryPendingSubmit->getEntry()';

        // Initialize
        $entry_id   = $this->entry_id;
        if ($entry_id < 1)
        {
            SBC::devError('Entry ID is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Entry Info
        $sql = 'SELECT id, user_id, challenge_id, comment_id, ispending, isdeleted
            FROM challenge_entries
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$entry_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Verify
        $entry_id   = isset($row['id']) ? (int) $row['id'] : 0;
        if ($entry_id < 1)
        {
            SBC::userError('Could not find entry in database');
        }

        // Set
        $this->user_id = $row['user_id'];

        // Deleted?
        if ($row['isdeleted'] == 1)
        {
            SBC::userError('Entry no longer exists');
        }
    
        // Is it still pending?
        if ($row['ispending'] != 1)
        {
            $db->close();
            header('Location: https://www.sketchbook.cafe/entry/'.$entry_id.'/');
            exit;
        }

        // Challenge ID
        $this->challenge_id = $row['challenge_id'];
        if ($this->challenge_id < 1)
        {
            SBC::devError('Challenge ID is not set for entry',$method);
        }

        // Comment ID
        $this->comment_id = $row['comment_id'];
        if ($this->comment_id < 1)
        {
            SBC::devError('Comment ID is not set for entry',$method);
        }
    }

    // Delete Entry
    final private function deleteEntry(&$db)
    {
        $method = 'EntryPendingSubmit->deleteEntry()';

        // Initialize
        $challenge_id   = $this->challenge_id;
        $entry_id       = $this->entry_id;
        $comment_id     = $this->comment_id;
        if ($entry_id < 1)
        {
            SBC::devError('Entry ID is not set',$method);
        }
        if ($challenge_id < 1)
        {
            SBC::devError('Challenge ID is not set',$method);
        }
        if ($comment_id < 1)
        {
            SBC::devError('Comment ID is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Current Image ID
        $sql = 'SELECT image_id 
            FROM sbc_comments
            WHERE id=?
            LIMIT 1';
        $stmt       = $db->prepare($sql);
        $stmt->bind_param('i',$comment_id);
        $row        = SBC::statementFetchRow($stmt,$db,$sql,$method);
        $image_id   = isset($row['image_id']) ? (int) $row['image_id'] : 0;

        // Delete entry_id and image_id from comment
        $sql = 'UPDATE sbc_comments
            SET entry_id=0,
            image_id=0
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$comment_id);
        SBC::statementExecute($stmt,$db,$sql,$method);

        // Mark image as deleted
        if ($image_id > 0)
        {
            $sql = 'UPDATE images
                SET isdeleted=1
                WHERE id=?
                LIMIT 1';
            $stmt = $db->prepare($sql);
            $stmt->bind_param('i',$image_id);
            SBC::statementExecute($stmt,$db,$sql,$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe_challenges');

        // Delete from challenge table
        $table_name = 'fc'.$challenge_id.'l';
        $sql = 'DELETE FROM '.$table_name.'
            WHERE entry_id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$entry_id);
        SBC::statementExecute($stmt,$db,$sql,$method);

        // Update gallery counters and images
        $ChallengeOrganizer = new ChallengeOrganizer($db);
        $ChallengeOrganizer->updateTotalEntries($challenge_id);
        $ChallengeOrganizer->updateLastImages($challenge_id);

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Mark entry as deleted
        $sql = 'UPDATE challenge_entries
            SET isdeleted=1
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$entry_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }

    // Approve Entry
    final private function approveEntry(&$db)
    {
        $method = 'EntryPendingSubmit->approveEntry()';

        // Initialize
        $challenge_id   = $this->challenge_id;
        $entry_id       = $this->entry_id;
        $table_name     = 'fc'.$challenge_id.'l';
        if ($entry_id < 1)
        {
            SBC::devError('Entry ID is not set',$method);
        }
        if ($challenge_id < 1)
        {
            SBC::devError('Challenge ID is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe_challenges');

        // Update Challenge Table
        $sql = 'UPDATE '.$table_name.'
            SET ispending=0
            WHERE entry_id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$entry_id);
        SBC::statementExecute($stmt,$db,$sql,$method);

        // Update challenge details
        $ChallengeOrganizer = new ChallengeOrganizer($db);
        $ChallengeOrganizer->updateTotalEntries($challenge_id);
        $ChallengeOrganizer->updateLastImages($challenge_id);
        $ChallengeOrganizer->updateDifficulty($challenge_id);

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Mark entry as no longer pending
        $sql = 'UPDATE challenge_entries
            SET ispending=0
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$entry_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }
}