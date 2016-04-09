<?php
/**
*
* Form Class
* 
* @author       Jonathan Maltezo (Kameloh)
* @copyright    (c) 2016, Jonathan Maltezo (Kameloh)
* @lastUpdated  2016-04-09
*
*/
class Form
{
    // Form Data
    public $data;
    public $field;
    public $hasinfo = 0;

    // Form Javascript
    public $onKeyPress = '';
    public $onSubmit = '';
    public $onKey = '';

    // Preview (array)
    public $preview;

    // Construct
    public function __construct($input)
    {
        // Initialize Vars
        $this->data['test']     = 1;
        $this->field['test']    = 1;

        // Name
        if (empty($input['name']))
        {
            error('Dev error: $name is not set for Form->construct()');
        }

        // Ajax
        $this->data['ajax'] = isset($input['ajax']) ? $input['ajax'] : 0;
        if ($this->data['ajax'] == 1)
        {
            // onSubmit
            $this->onSubmit = ' '.$input['javascript'].' return false; ';
        }

        // Set Values
        $this->data['name']             = $input['name'];
        $this->data['action']           = isset($input['action']) ? $input['action'] : '';
        $this->data['method']           = isset($input['method']) ? $input['method'] : 'POST';
        $this->data['button_class']     = isset($input['button_class']) ? $input['button_class'] : 'button';
        $this->data['submit_class']     = isset($input['submit_class']) ? $input['submit_class'] : 'submit';
        $this->data['inactive']         = isset($input['inactive']) ? $input['inactive'] : 'Submit';
        $this->data['active']           = isset($input['active']) ? $input['active'] : 'Submitting...';

        // Submit ID
        $this->data['submit_id'] = $this->data['name'] . '_submit';

        // Create Vars
        $this->onKeyPress   .= 'formOnKeyPress(\''.$this->data['submit_id'].'\', \''.$this->data['inactive'].'\'); sbc_button_sumbit_enable();';
        $this->onSubmit     = 'formOnSubmit (\''.$this->data['submit_id'].'\', \''.$this->data['active'].'\');';
        $this->onKey        = ' onkeypress="'.$this->onKeyPress.'" onsubmit="'.$this->onSubmit.'" ';

        // Set has info
        $this->hasinfo = 1;
    }
}