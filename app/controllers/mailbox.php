<?php
// Mailbox

class Mailbox extends Controller
{
    // Construct
    public function __construct()
    {
    }

    // Compose Note
    public function compose()
    {
        $ComposeObject  = $this->model('ComposeNotePage');
        $Form           = $ComposeObject->form;

        // View
        $this->view('mailbox/composenote', ['Form' => $Form]);
    }
    public function compose_submit()
    {
        $this->model('ComposeNoteSubmit');
    }

    // Main Page
    public function index()
    {
        $this->model('MailboxPage');
        $this->view('mailbox/index');
    }


}