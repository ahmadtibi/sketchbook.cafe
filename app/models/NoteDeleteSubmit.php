<?php

class NoteDeleteSubmit
{
    private $user_id = 0;
    private $r_user_id = 0;
    private $mail_id = 0;
    private $who = '';

    // Construct
    public function __construct(&$obj_array)
    {
        // Initialize Objects
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // Classes and Functions
        sbc_class('CountMail');

        // Get Mail ID
        $mail_id    = isset($_POST['mail_id']) ? (int) $_POST['mail_id'] : 0;
        if ($mail_id < 1)
        {
            error('Dev error: $mail_id is not set for NoteDeleteSubmit->construct()');
        }
        $this->mail_id  = $mail_id;

        // Open Connection
        $db->open();

        // User Required
        $User->required($db);
        $user_id = $User->getUserId();
        $this->user_id = $user_id;

        // Get Note Information
        $this->getNoteInfo($db);

        // Remove from the user's mailbox table
        $this->removeFromTable($db);

        // Update Mail Thread
        $this->updateMailThread($db);

        // Calculate Deleted
        $this->calculateDeleted($db);

        // Count Mail
        $CountMail  = new CountMail($user_id);
        $CountMail->process($db);

        // Close Connection
        $db->close();

        // Header
        header('Location: https://www.sketchbook.cafe/mailbox/');
        exit;
    }

    // Get Note Information
    final private function getNoteInfo(&$db)
    {
        // Initialize Vars
        $mail_id    = $this->mail_id;
        $user_id    = $this->user_id;

        // Verify
        if ($mail_id < 1 || $user_id < 1)
        {
            error('Dev error: $mail_id or $user_id is not set for NoteDeleteSubmit->getNoteInfo()');
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Note Information
        $sql = 'SELECT id, user_id, r_user_id
            FROM mailbox_threads
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$mail_id);
        if (!$stmt->execute())
        {
            error('Could not execute statement (get note information) for NoteDeleteSubmit->getNoteInfo()');
        }
        $result = $stmt->get_result();
        $row    = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        $stmt->close();

        // Set
        $mail_id    = isset($row['id']) ? (int) $row['id'] : 0;
        if ($mail_id < 1)
        {
            error('Dev error: Could not find mail thread in database for NoteDeleteSubmit->getNoteInfo()');
        }

        // Do they have permission?
        if ($row['user_id'] != $user_id)
        {
            if ($row['r_user_id'] != $user_id)
            {
                error('Sorry, you do not have permission to delete this mail thread');
            }
        }

        // Who are they?
        if ($row['user_id'] == $user_id)
        {
            $this->who          = 'user_id';
            $this->r_user_id    = $row['r_user_id'];
        }
        else
        {
            $this->who          = 'r_user_id';
            $this->r_user_id    = $row['user_id'];
        }
    }

    // Remove From User's Table
    final private function removeFromTable(&$db)
    {
        // Initialize Vars
        $mail_id    = $this->mail_id;
        $user_id    = $this->user_id;

        // Verify
        if ($user_id < 1 || $mail_id < 1)
        {
            error('Dev error: $user_id or $mail_id is not set for NoteDeleteSubmit->removeFromTable()');
        }

        // Switch
        $db->sql_switch('sketchbookcafe_users');

        // Set Table
        $table_name = 'u'.$user_id.'m';

        // Delete from their mailbox table
        $sql = 'DELETE FROM '.$table_name.'
            WHERE cid=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$mail_id);
        if (!$stmt->execute())
        {
            error('Could not execute statement (delete from mailbox table) for NoteDeleteSubmit->removeFromTable()');
        }
        $stmt->close();
    }

    // Update Mail Thread
    final private function updateMailThread(&$db)
    {
        // Initialize Vars
        $user_id    = $this->user_id;
        $mail_id    = $this->mail_id;
        $who        = $this->who;

        // Check
        if ($user_id < 1 || $mail_id < 1 || empty($who))
        {
            error('Dev error: $user_id:'.$user_id.', $mail_id:'.$mail_id.', $who:'.$who.' for NoteDeleteSubmit->updateMailThread()');
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Update
        $sql = 'UPDATE mailbox_threads
            SET removed_'.$who.'=1,
            isremoved=1
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$mail_id);
        if (!$stmt->execute())
        {
            error('Could not execute statement (update mailbox thread) for NoteDeleteSubmit->updateMailThread()');
        }
        $stmt->close();
    }

    // Calculate Deleted
    private function calculateDeleted(&$db)
    {
        // Initialize Vars
        $mail_id    = $this->mail_id;
        if ($mail_id < 1)
        {
            error('Dev error: $mail_id is not set for NoteDeleteSubmit->calculateDelete()');
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Thread Info
        $sql    = 'SELECT id, removed_user_id, removed_r_user_id
            FROM mailbox_threads
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$mail_id);
        if (!$stmt->execute())
        {
            error('Could not execute statement (get thread info) for NoteDeleteSubmit->calculateDelete()');
        }
        $result = $stmt->get_result();
        $row    = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        $stmt->close();

        // Verify
        $mail_id    = isset($row['id']) ? (int) $row['id'] : 0;
        if ($mail_id < 1)
        {
            return null;
        }

        // Calculate
        $isdeleted = 0;
        if ($row['removed_user_id'] == 1 && $row['removed_r_user_id'] == 1)
        {
            // Set
            $isdeleted = 1;
        }

        // Update
        $sql = 'UPDATE mailbox_threads
            SET isdeleted=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ii',$isdeleted,$mail_id);
        if (!$stmt->execute())
        {
            error('Could not execute statement (update mailbox thread) for NoteDeleteSubmit->calculateDelete()');
        }
        $stmt->close();
    }
}