<?php

class Register extends Controller
{
    private $db;

    public function __construct()
    {
        global $db;
        $this->db = &$db;
    }

    // Submit
    public function submit()
    {
        $this->model('UserRegistration');
    }

    // Main Registration Page
    public function index()
    {
        // Database
        $db = $this->db;
        $db->open();
        $sql = 'SELECT username FROM users WHERE id=2 LIMIT 1';
        $result = $db->sql_query($sql);
        $row    = $db->sql_fetchrow($result);
        echo 'username is '.$row['username'];
        $db->close();

        // Model
        $registerObject = $this->model('UserRegistrationPage');
        $Form           = $registerObject->form;

        // Use Variables instead of the whole object

        // View
        $this->view('sketchbookcafe/header');
        $this->view('register/index', ['Form' => $Form]);
        $this->view('sketchbookcafe/footer');
    }
}