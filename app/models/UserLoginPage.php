<?php
class UserLoginpage
{
    public $form = [];

    // Construct
    public function __construct(&$obj_array)
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
        $Form->field['ip_lock'] = $Form->checkbox(array
        (
            'name'      => 'ip_lock',
            'value'     => 1,
            'checked'   => 1,
        ));

        // Set vars
        $this->form = $Form;
    }
}