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
    public $pagenumbers = '';

    // Total
    public $total_replies = 0;

    // SQL
    private $mail_user_id = 0;
    private $mail_r_user_id = 0;
    private $db = '';
    private $User = '';
    public $result = [];
    public $rownum = 0;
    public $comment_id      = 0;

    // Data
    public $data    = [];

    // Construct
    public function __construct(&$obj_array)
    {
        // Initialize Objects
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // Set
        $this->db   = $db;
        $this->User = $User;
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
        // Classes
        sbc_class('Form');
        sbc_class('TextareaSettings');
        sbc_class('PageNumbers');

        // Initialize Vars
        $db     = $this->db;
        $User   = $this->User;

        // Initialize Vars
        $mail_id    = $this->mail_id;
        if ($mail_id < 1)
        {
            error('Dev error: $mail_id is not set for NotePage->getNote()');
        }

        // Open Connection
        $db->open();

        // User Required
        $User->required($db);
        $user_id        = $User->getUserId();
        $this->user_id  = $user_id;

        // Get Thread Info
        $this->getMailThread($db);

        // Page Numbers (total must be after getMailThread)
        $ppage          = 1;
        $pageno         = $this->pageno;
        $total          = $this->total_replies;
        $offset         = $pageno * $ppage;
        $pages_link     = 'https://www.sketchbook.cafe/mailbox/note/'.$mail_id.'/{page_link}/';

        // Get Comments and Process
        $this->getComments($db);

        // Process Data should be at the end
        $ProcessAllData = new ProcessAllData();

        // Close Connection
        $db->close();

        // Page numbers
        $PageNumbersObject  = new PageNumbers(array
        (
            'name'          => 'pagenumbers',
            'first'         => 0, // current page
            'current'       => $pageno, // page number
            'posts'         => $total, // count(*) value
            'ppage'         => $ppage, // max number of posts per page
            'display'       => 4, // numbers of pages to display as links per side
            'link'          => $pages_link, 
            'css_overlay'   => '',
            'css_inactive'  => 'pageNumbersItem pageNumbersItemUnselected',
            'css_active'    => 'pageNumbersItem pageNumbersItemSelected',
        ));
        $this->pagenumbers  = $PageNumbersObject->getPageNumbers();

        // Textarea Settings
        $TextareaSettings = new TextareaSettings('notereply');
        $TextareaSettings->setValue('');
        $message_settings   = $TextareaSettings->getSettings();

        // New Form
        $Form = new Form(array
        (
            'name'      => 'noteform',
            'action'    => 'https://www.sketchbook.cafe/mailbox/note_submit/',
            'method'    => 'POST',
        ));

        // Reply Message
        $Form->field['message'] = $Form->textarea($message_settings);

        // Hidden ID
        $Form->field['id'] = $Form->hidden(array
        (
            'name'  => 'mail_id',
            'value' => $mail_id,
        ));

        // Set Vars
        $this->Form = $Form;
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
        $sql = 'SELECT id, user_id, r_user_id, date_updated, title, comment_id, removed_user_id, removed_r_user_id, total_replies, isdeleted
            FROM mailbox_threads
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$mail_id);
        if (!$stmt->execute())
        {
            error('Could not execute statement (get mail thread information) for NotePage->getMailThread()');
        }
        $result = $stmt->get_result();
        $row    = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        $stmt->close();

        // ID?
        $mail_id    = isset($row['id']) ? (int) $row['id'] : 0;
        if ($mail_id < 1)
        {
            error('Could not find mail in database');
        }

        // Check if the user can view this mail thread
        if ($row['user_id'] != $user_id)
        {
            // Other?
            if ($row['r_user_id'] != $user_id)
            {
                error('Sorry, you do not have permission to view this mail thread');
            }
        }

        // Which user is the user?
        $who    = '';
        if ($row['user_id'] == $user_id)
        {
            $who = 'user_id';
        }
        else if ($row['r_user_id'] == $user_id)
        {
            $who = 'r_user_id';
        }
        if (empty($who))
        {
            error('Dev error: $who is not set for NotePage->getMailThread()');
        }

        // Check if that user removed this thread from their mailbox
        if ($row['removed_'.$who] != 0)
        {
            error('Mail no longer exists in your mailbox');
        }

        // Check if it's deleted
        if ($row['isdeleted'] == 1)
        {
            error('Mail no longer exists');
        }

        // Set Data
        $this->data             = $row;
        $this->comment_id       = $row['comment_id'];
        $this->mail_user_id     = $row['user_id'];
        $this->mail_r_user_id   = $row['r_user_id'];
        $this->total_replies    = $row['total_replies'];
    }

    // Get Comments
    final private function getComments(&$db)
    {
        // Globals (for now)
        global $Comment,$Member;

        // Initialize Vars
        $mail_id    = $this->mail_id;
        $pageno     = $this->pageno;
        if ($pageno < 1)
        {
            $pageno = 0;
        }
        if ($mail_id < 1)
        {
            error('Dev error: $mail_id is not set for NotePage->getComments()');
        }

        // Switch
        $db->sql_switch('sketchbookcafe_mailbox');

        // Get Comments
        $table_name = 'm'.$mail_id.'x';
        $sql = 'SELECT cid
            FROM '.$table_name.'
            ORDER BY id
            ASC';
        $result = $db->sql_query($sql);
        $rownum = $db->sql_numrows($result);

        // Set Vars
        $this->result   = $result;
        $this->rownum   = $rownum;

        // Add Comment ID
        $Comment->idAddOne($this->comment_id);
        $Comment->idAddRows($result,'cid');
        $Member->idAddOne($this->mail_user_id);
        $Member->idAddOne($this->mail_r_user_id);
    }
}