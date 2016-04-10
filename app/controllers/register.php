<?php

class Register extends Controller
{
    public function __construct()
    {

    }

    // Submit
    public function submit()
    {
        // Functions
        sbc_function('get_username');

        // Username
        $username = isset($_POST['username']) ? get_username($_POST['username']) : '';


        echo 'username is '.$username;
        $this->model('UserRegistration');
        // $this->view('register/index');
    }

    // Main Registration Page
    public function index()
    {
        // Classes
        sbc_class('Form');

        $Form = new Form(array(
            'name'          => 'registerForm',
            'action'        => 'https://www.sketchbook.cafe/register/submit/',
            'method'        => 'POST',

        ));

        // Submit
        $Form->field['submit'] = $Form->submit(array
        (
            'name'  => 'submit',
            'css'   => '',
        ));

        // Username
        $Form->field['username'] = $Form->input(array
        (
            'name'          => 'username',
            'type'          => 'text',
            'max'           => 20,
            'placeholder'   => 'username',
        ));

        // E-mail
        $Form->field['email1']   = $Form->input(array
        (
            'name'          => 'email1',
            'type'          => 'text',
            'max'           => 100,
            'placeholder'   => 'e-mail',
        ));

        // E-mail again?
        $Form->field['email2']  = $Form->input(array
        (
            'name'          => 'email2',
            'type'          => 'text',
            'max'           => 100,
            'placeholder'   => 'e-mail again',
        ));

        // Terms of Service
        $Form->field['termsofservice'] = $Form->checkbox(array
        (
            'name'      => 'termsofservice',
            'value'     => 1,
        ));

        $this->view('register/index', ['Form' => $Form]);
    }
}