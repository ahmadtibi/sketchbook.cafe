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
        // Classes
        sbc_class('Form');

        // New Form
        $Form = new Form(array(
            'name'      => 'loginform',
            'action'    => 'https://www.sketchbook.cafe/login/submit/',
            'method'    => 'POST',
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

        // Password
        $Form->field['password'] = $Form->input(array
        (
            'name'          => 'password',
            'type'          => 'password',
            'max'           => 100,
            'placeholder'   => 'password',
        ));

        // IP Lock
        $Form->field['iplock'] = $Form->checkbox(array
        (
            'name'      => 'iplock',
            'value'     => 1,
            'checked'   => 1,
        ));

        // View
        $this->view('login/index', ['Form' => $Form]);
    }
}