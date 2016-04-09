<?php

class Register extends Controller
{
    public function __construct()
    {

    }

    public function index()
    {
        // Classes
        sbc_class('Form');

        $Form = new Form(array(
            'name'          => 'registerForm',
            'action'        => 'https://www.sketchbook.cafe/action/',
            'method'        => 'POST',

        ));

        // Form Dropdown Test
        $input      = array
        (
            'name'  => 'dothis',
        );
        $list = array
        (
            'test1' => 10000,
            'test2' => 20000,
            'test3' => 48829, 
            'test4' => 99999,
        );
        $current_value = 48829;
        $Form->field['dothis'] = $Form->dropdown($input,$list,$current_value);

        // Submit
        $Form->field['submit'] = $Form->submit(array
        (
            'name'  => 'submit',
            'css'   => '',
        ));

        // Hidden
        $Form->field['testmanhero'] = $Form->hidden(array
        (
            'name'  => 'testmanhero',
            'value' => 'kuva',
        ));

        // Username
        $Form->field['username'] = $Form->input(array
        (
            'name'          => 'username',
            'type'          => 'text',
            'max'           => 20,
            'placeholder'   => 'username',
        ));

        // Terms of Service
        $Form->field['termsofservice'] = $Form->checkbox(array
        (
            'name'      => 'termsofservice',
            'value'     => 1,
        ));

        // Textarea
        $Form->field['mycomment'] = $Form->textarea(array
        (
            'name'          => 'mycomment',
            'max'           => 100,
            'help'          => 1,
            'preview'       => 1,
            'css'           => 'textarea_testmanhero',
            'placeholder'   => 'Yo write some text here!',
        ));

        $this->view('register/index', ['Form' => $Form]);
    }
}