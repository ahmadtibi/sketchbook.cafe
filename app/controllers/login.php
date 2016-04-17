<?php

class Login extends Controller
{
    protected $obj_array = '';

    public function __construct(&$obj_array)
    {
        $this->obj_array = &$obj_array;
    }

    // Login Page
    public function index()
    {
        // Model
        $loginObject    = $this->model('UserLoginPage',$this->obj_array);
        $Form           = $loginObject->form;

        // View
        $this->view('sketchbookcafe/header');
        $this->view('login/index', ['Form' => $Form]);
        $this->view('sketchbookcafe/footer');
    }
    public function submit()
    {
        $this->model('UserLogin',$this->obj_array);
    }
}