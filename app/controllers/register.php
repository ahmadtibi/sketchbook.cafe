<?php

class Register extends Controller
{
    public function __construct()
    {

    }

    // Submit
    public function submit()
    {

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
        $Form->field['email']   = $Form->input(array
        (
            'name'          => 'email',
            'type'          => 'text',
            'max'           => 100,
            'placeholder'   => 'e-mail',
        ));

        // Password
        $Form->field['pass1']   = $Form->input(array
        (
            'name'          => 'pass1',
            'type'          => 'password',
            'max'           => 100,
            'placeholder'   => 'password',
        ));

        // Password Again
        $Form->field['pass2']   = $Form->input(array
        (
            'name'          => 'pass2',
            'type'          => 'password',
            'max'           => 100,
            'placeholder'   => 'password',
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