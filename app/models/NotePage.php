<?php
// @author          Jonathan Maltezo (Kameloh)
// @lastUpdated     2016-04-27

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\Form\Form as Form;
use SketchbookCafe\TextareaSettings\TextareaSettings as TextareaSettings;
use SketchbookCafe\PageNumbers\PageNumbers as PageNumbers;

// Note View Page
class NotePage
{
    private $mail_id = 0;
    private $user_id = 0;
    public $Form = [];
    public $DeleteForm = [];

    // Page Numbers
    private $pageno = 0;
    private $ppage = 20; // 20 comments per page
    private $pages = 0;
    private $offset = 0;
    public $pagenumbers = '';
    public $pages_min = 0;
    public $pages_max = 0;
    public $pages_total = 0;

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

    // Objects
    private $obj_array = [];

    // Construct
    public function __construct(&$obj_array)
    {
        $method = 'NotePage->__construct()';

        // Initialize Objects
        $db                 = &$obj_array['db'];
        $User               = &$obj_array['User'];
        $this->obj_array    = &$obj_array;

        // Set
        $this->db   = $db;
        $this->User = $User;
    }

    // Set Page Number
    final public function setPageNumber($number)
    {
        $method = 'NotePage->setPageNumber';

        $this->pageno = isset($number) ? (int) $number : 0;
        if ($this->pageno < 1)
        {
            $this->pageno = 0;
        }
    }

    // Set Note ID
    final public function setNoteId($mail_id)
    {
        $method = 'NotePage->setNoteId()';

        // Initialize Vars
        $mail_id    = isset($mail_id) ? (int) $mail_id : 0;
        if ($mail_id < 1)
        {
            SBC::devError('$mail_id is not set',$method);
        }

        // Set vars
        $this->mail_id = $mail_id;
    }

    // Get Note
    final public function getNote()
    {
        $method = 'NotePage->getNote()';

        // Initialize Objects
        $db     = $this->db;
        $User   = $this->User;

        // Initialize Vars
        $mail_id    = $this->mail_id;
        if ($mail_id < 1)
        {
            SBC::devError('$mail_id is not set',$method);
        }

        // Open Connection
        $db->open();

        // User Required
        $User->setFrontpage();
        $User->required($db);
        $user_id        = $User->getUserId();
        $this->user_id  = $user_id;

        // Get Thread Info
        $this->getMailThread($db);

        // Mark thread as read
        $this->markMailAsRead($db);

        // Page Numbers (total must be after getMailThread)
        $ppage          = 10;
        $pageno         = $this->pageno;
        $total          = $this->total_replies;
        $pages_link     = 'https://www.sketchbook.cafe/mailbox/note/'.$mail_id.'/{page_link}/';

        // Page Numbers (SQL)
        $offset         = $pageno * $ppage;
        $this->offset   = $offset;
        $this->ppage    = $ppage;
        $this->pages    = ceil($total / $ppage);

        // Get Comments and Process
        $this->getComments($db);

        // Force the user to have a mailbox update
        $User->forceMailboxUpdate($db);

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

        // More Pagenumbers
        $this->pages_min    = $PageNumbersObject->pages_min;
        $this->pages_max    = $PageNumbersObject->pages_max;
        $this->pages_total  = $PageNumbersObject->pages_total;

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

        // Delete Form DeleteForm
        $DeleteForm = new Form(array
        (
            'name'      => 'deleteform',
            'action'    => 'https://www.sketchbook.cafe/mailbox/note_delete_submit/',
            'method'    => 'POST',
        ));

        // Hidden
        $DeleteForm->field['mail_id'] = $DeleteForm->hidden(array
        (
            'name'      => 'mail_id',
            'value'     => $mail_id,
        ));

        // Submit
        $DeleteForm->field['submit'] = $DeleteForm->submit(array
        (
            'name'      => 'submit',
            'css'       => 'deleteSubmit',
            'value'     => 'Yes',
        ));

        // Set Vars
        $this->Form         = $Form;
        $this->DeleteForm   = $DeleteForm;
    }

    // Get Mail Thread
    final private function getMailThread(&$db)
    {
        $method = 'NotePage->getMailThread()';

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
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$mail_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // ID?
        $mail_id    = isset($row['id']) ? (int) $row['id'] : 0;
        if ($mail_id < 1)
        {
            SBC::userError('Could not find mail in database');
        }

        // Check if the user can view this mail thread
        if ($row['user_id'] != $user_id)
        {
            // Other?
            if ($row['r_user_id'] != $user_id)
            {
                SBC::userError('Sorry, you do not have permission to view this mail thread');
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
            SBC::devError('$who is not set',$method);
        }

        // Check if that user removed this thread from their mailbox
        if ($row['removed_'.$who] != 0)
        {
            SBC::userError('Mail no longer exists in your mailbox');
        }

        // Check if it's deleted
        if ($row['isdeleted'] == 1)
        {
            SBC::userError('Mail no longer exists');
        }

        // Set Data
        $this->data             = $row;
        $this->comment_id       = $row['comment_id'];
        $this->mail_user_id     = $row['user_id'];
        $this->mail_r_user_id   = $row['r_user_id'];
        $this->total_replies    = $row['total_replies'];
    }

    // Mark mail as read in the user's mailbox
    final private function markMailAsRead(&$db)
    {
        $method = 'NotePage->markMailAsRead()';

        // Initialize Vars
        $user_id    = $this->user_id;
        $mail_id    = $this->mail_id;

        // Check just in case
        if ($user_id < 1 || $mail_id < 1)
        {
            SBC::devError('$user_id or $mail_id is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe_users');

        // Set Table
        $table_name = 'u'.$user_id.'m';

        // Mark thread as read
        $sql = 'UPDATE '.$table_name.'
            SET isnew=0
            WHERE cid=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$mail_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }

    // Get Comments
    final private function getComments(&$db)
    {
        $method = 'NotePage->getComments()';

        // Initialize Objects
        $Comment    = $this->obj_array['Comment'];
        $Member     = $this->obj_array['Member'];

        // Initialize Vars
        $offset     = $this->offset;
        $ppage      = $this->ppage;
        $mail_id    = $this->mail_id;
        $pageno     = $this->pageno;
        if ($pageno < 1)
        {
            $pageno = 0;
        }
        if ($mail_id < 1)
        {
            SBC::devError('$mail_id is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe_mailbox');

        // Get Comments
        $table_name = 'm'.$mail_id.'x';
        $sql = 'SELECT cid
            FROM '.$table_name.'
            ORDER BY id
            ASC
            LIMIT '.$offset.', '.$ppage;
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