<?php
// Mailbox

class Mailbox extends Controller
{
    // Construct
    public function __construct()
    {
    }

    // View Note
    public function note($mail_id = 0, $pageno = 0)
    {
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
        $noteObject = $this->model('NotePage');
        $noteObject->setNoteId($mail_id);
        $noteObject->setPageNumber($pageno);
        $noteObject->getNote();

        // View
        $current_page   = 'viewnote';
        require 'header.php';
        require 'mailbox_top.php';
        $this->view('mailbox/viewnote');
        require 'mailbox_bottom.php';
        require 'footer.php';
    }

    // Compose Note
    public function compose()
    {
        $ComposeObject  = $this->model('ComposeNotePage');
        $Form           = $ComposeObject->form;

        // View
        $current_page   = 'compose';
        require 'header.php';
        require 'mailbox_top.php';
        $this->view('mailbox/composenote', ['Form' => $Form]);
        require 'mailbox_bottom.php';
        require 'footer.php';
    }
    public function compose_submit()
    {
        $noteObject     = $this->model('ComposeNoteSubmit');
        $mail_id        = $noteObject->mail_id;
    }

    // Main Page
    public function index()
    {
        $this->model('MailboxPage');

        // View
        $current_page   = 'inbox';
        require 'header.php';
        require 'mailbox_top.php';
        $this->view('mailbox/index');
        require 'mailbox_bottom.php';
        require 'footer.php';
    }

}