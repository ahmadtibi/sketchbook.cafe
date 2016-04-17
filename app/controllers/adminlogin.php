<?php

class AdminLogin extends Controller
{
    protected $obj_array = '';

    public function __construct(&$obj_array)
    {
        $this->obj_array = &$obj_array;
    }

    // Admin Login Page
    public function index()
    {
        // Model
        $AdminLogin = $this->model('AdminLoginPage',$this->obj_array);
        $Form       = $AdminLogin->form;

        // View
        $this->view('sketchbookcafe/header');
        $this->view('adminlogin/index', ['Form' => $Form]);
        $this->view('sketchbookcafe/footer');
    }
    public function submit()
    {
        // Model
        $this->model('AdminLoginSubmit',$this->obj_array);
    }
}