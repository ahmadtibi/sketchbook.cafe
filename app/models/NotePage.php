<?php
// Note View Page
class NotePage
{
    private $mail_id = 0;
    private $user_id = 0;
    public $Form = [];

    // Page Numbers
    private $pageno = 0;
    private $ppage = 20; // 20 comments per page

    // SQL
    public $comments_result = [];
    public $comments_rownun = 0;

    // Construct
    public function __construct()
    {
    }

    // Set Page Number
    final public function setPageNumber($number)
    {
        $this->pageno = isset($number) ? (int) $number : 0;
        if ($this->pageno < 1)
        {
            $this->pageno = 0;
        }
    }

    // Set Note ID
    final public function setNoteId($mail_id)
    {
        // Initialize Vars
        $mail_id    = isset($mail_id) ? (int) $mail_id : 0;
        if ($mail_id < 1)
        {
            error('Dev error: $mail_id is not set for NotePage->setNoteId()');
        }

        // Set vars
        $this->mail_id = $mail_id;
    }

    // Get Note
    final public function getNote()
    {
        // Initialize Vars
        $mail_id    = $this->mail_id;
        if ($mail_id < 1)
        {
            error('Dev error: $mail_id is not set for NotePage->getNote()');
        }

        // Globals
        global $db,$User;

        // Open Connection
        $db->open();

        // User Required
        $User->required($db);
        $user_id        = $User->getUserId();
        $this->user_id  = $user_id;
        $ProcessAllData = new ProcessAllData();

        // Get Thread Info
        $this->getMailThread($db);

        // Close Connection
        $db->close();
    }

    // Get Mail Thread
    final private function getMailThread(&$db)
    {
        // Initialize Vars
        $mail_id    = $this->mail_id;
        $user_id    = $this->user_id;

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get thread information
        $sql = 'SELECT id, user_id, r_user_id, date_updated, title, comment_id, removed_user_id, removed_r_user_id, isdeleted
            FROM mailbox_threads
            WHERE id=?
            LIMIT 1';

        // We're here!

    }
}