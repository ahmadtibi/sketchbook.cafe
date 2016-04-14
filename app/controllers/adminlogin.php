<?php

class AdminLogin extends Controller
{
    public function __construct()
    {
    }

    // Submit
    public function submit()
    {
        // Model
        $this->model('AdminLoginSubmit');
    }

    // Main Page
    public function index()
    {
        // Model
        $AdminLogin = $this->model('AdminLoginPage');
        $Form       = $AdminLogin->form;
        $this->view('adminlogin/index', ['Form' => $Form]);
    }
}