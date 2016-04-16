<?php

class Login extends Controller
{
    public function __construct()
    {
    }

    // Submit
    public function submit()
    {
        $this->model('UserLogin');
    }

    // Main Page
    public function index()
    {
        // Model
        $loginObject    = $this->model('UserLoginPage');
        $Form           = $loginObject->form;

        // View
        require('header.php');
        $this->view('login/index', ['Form' => $Form]);
        require('footer.php');
    }
}