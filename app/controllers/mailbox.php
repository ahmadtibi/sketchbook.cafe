<?php
// @author          Kameloh
// @lastUpdated     2016-05-11

class Mailbox extends Controller
{
    protected $obj_array = '';

    // Construct
    public function __construct(&$obj_array)
    {
        $this->obj_array = &$obj_array;
    }

    // Delete Note
    public function note_delete_submit()
    {
        // Model
        $this->model('NoteDeleteSubmit',$this->obj_array);
    }

    // View Note
    public function note($mail_id = 0, $pageno = 0)
    {
        // Initialize Objects
        $Comment    = $this->obj_array['Comment'];
        $Member     = $this->obj_array['Member'];
        $User       = $this->obj_array['User'];

        // Mail ID
        $mail_id    = isset($mail_id) ? (int) $mail_id : 0;
        if ($mail_id < 1)
        {
            error('Note ID not set');
        }

        // Page Numbers
        $pageno     = isset($pageno) ? (int) $pageno : 0;
        if ($pageno < 1)
        {
            $pageno = 0;
        }

        // Model
        $noteObject = $this->model('NotePage',$this->obj_array);
        $noteObject->setNoteId($mail_id);
        $noteObject->setPageNumber($pageno);
        $noteObject->getNote();

        // Compose Note Obj
        $ComposeNoteObj = $this->model('ComposeNoteForm');
        $ComposeForm    = $ComposeNoteObj->getForm();

        // Set Vars
        $DeleteForm     = $noteObject->DeleteForm;
        $Form           = $noteObject->Form;
        $result         = $noteObject->result;
        $rownum         = $noteObject->rownum;
        $comment_id     = $noteObject->comment_id;
        $Mail           = $noteObject->data;
        $current_page   = 'viewnote';
        $total_replies  = $noteObject->total_replies;
        $pagenumbers    = $noteObject->pagenumbers;
        $pages_min      = $noteObject->pages_min;
        $pages_max      = $noteObject->pages_max;
        $pages_total    = $noteObject->pages_total;

        // View
        $this->view('sketchbookcafe/header');
        $this->view('sketchbookcafe/mailbox_top', 
        [
            'current_page'  => $current_page,
            'User'          => $User,
            'ComposeForm'   => $ComposeForm,
        ]);
        $this->view('mailbox/viewnote', 
        [
            'DeleteForm'    => $DeleteForm,
            'Form'          => $Form,
            'result'        => $result,
            'rownum'        => $rownum, 
            'Mail'          => $Mail,
            'Comment'       => $Comment,
            'Member'        => $Member,
            'User'          => $User,
            'pageno'        => $pageno,
            'total_replies' => $total_replies,
            'pagenumbers'   => $pagenumbers,
            'pages_min'     => $pages_min,
            'pages_max'     => $pages_max,
            'pages_total'   => $pages_total,
        ]);
        $this->view('sketchbookcafe/mailbox_bottom');
        $this->view('sketchbookcafe/footer');
    }
    public function note_submit()
    {
        // Model
        $this->model('NoteReply',$this->obj_array);
    }

/*
    // Compose Note
    public function compose()
    {
        $User           = $this->obj_array['User'];
        $ComposeObject  = $this->model('ComposeNotePage',$this->obj_array);
        $Form           = $ComposeObject->form;

        // Compose Note Obj
        $ComposeNoteObj = $this->model('ComposeNoteForm');
        $ComposeForm    = $ComposeNoteObj->getForm();

        // Current Page
        $current_page   = 'compose';

        // View
        $this->view('sketchbookcafe/header');
        $this->view('sketchbookcafe/mailbox_top', 
        [
            'current_page'  => $current_page,
            'User'          => $User,
            'ComposeForm'   => $ComposeForm,
        ]);
        $this->view('mailbox/composenote', ['Form' => $Form]);
        $this->view('sketchbookcafe/mailbox_bottom');
        $this->view('sketchbookcafe/footer');
    }
*/
    public function compose_submit()
    {
        $noteObject     = $this->model('ComposeNoteSubmit',$this->obj_array);
        $mail_id        = $noteObject->mail_id;
    }

    // Main Page
    public function index($pageno = 0)
    {
        // Initialize Objects
        $Member     = $this->obj_array['Member'];
        $User       = $this->obj_array['User'];

        // Page Numbers
        $pageno     = isset($pageno) ? (int) $pageno : 0;
        if ($pageno < 1)
        {
            $pageno = 0;
        }

        // Model
        $InboxObject    = $this->model('MailboxPage');
        $InboxObject->setPageNumber($pageno);
        $InboxObject->processPage($this->obj_array);

        // Compose Note Obj
        $ComposeNoteObj = $this->model('ComposeNoteForm');
        $ComposeForm    = $ComposeNoteObj->getForm();

        // Vars
        $user_id        = $InboxObject->user_id;
        $result         = $InboxObject->result;
        $rownum         = $InboxObject->rownum;
        $isnew          = $InboxObject->isnew;
        $pagenumbers    = $InboxObject->pagenumbers;
        $pages_min      = $InboxObject->pages_min;
        $pages_max      = $InboxObject->pages_max;
        $pages_total    = $InboxObject->pages_total;

        // Current Page
        $current_page   = 'inbox';

        // View
        $this->view('sketchbookcafe/header');
        $this->view('sketchbookcafe/mailbox_top', 
        [
            'current_page'  => $current_page,
            'User'          => $User,
            'ComposeForm'   => $ComposeForm,
        ]);
        $this->view('mailbox/index',
        [
            'Member'        => $Member,
            'result'        => $result,
            'rownum'        => $rownum,
            'User'          => $User,
            'user_id'       => $user_id,
            'isnew'         => $isnew,
            'pagenumbers'   => $pagenumbers,
            'pages_min'     => $pages_min,
            'pages_max'     => $pages_max,
            'pages_total'   => $pages_total,
        ]);
        $this->view('sketchbookcafe/mailbox_bottom');
        $this->view('sketchbookcafe/footer');
    }

}