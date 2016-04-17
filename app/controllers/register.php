<?php

class Register extends Controller
{
    protected $obj_array = '';

    public function __construct(&$obj_array)
    {
        $this->obj_array = &$obj_array;
    }

    // Main Registration Page
    public function index()
    {
        // Model
        $registerObject = $this->model('UserRegistrationPage',$this->obj_array);
        $Form           = $registerObject->form;

        // View
        $this->view('sketchbookcafe/header');
        $this->view('register/index', ['Form' => $Form]);
        $this->view('sketchbookcafe/footer');
    }
    public function submit()
    {
        // Model
        $this->model('UserRegistration',$this->obj_array);
    }
}