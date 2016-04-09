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
        $this->onKeyPress   = 'formOnKeyPress(\''.$this->data['submit_id'].'\', \''.$this->data['inactive'].'\'); sbc_button_sumbit_enable();';
        $this->onSubmit     = 'formOnSubmit (\''.$this->data['submit_id'].'\', \''.$this->data['active'].'\');';
        $this->onKey        = ' onkeypress="'.$this->onKeyPress.'" onsubmit="'.$this->onSubmit.'" ';

        // Ajax
        $this->data['ajax'] = isset($input['ajax']) ? $input['ajax'] : 0;
        if ($this->data['ajax'] == 1)
        {
            // onSubmit
            $this->onSubmit .= ' '.$input['javascript'].' return false; ';
        }

        // Set has info
        $this->hasinfo = 1;
    }

    // Start Form
    final public function start()
    {
        // Make sure this form has base information
        if ($this->hasinfo != 1)
        {
            error('Dev error: $hasinfo is not set for Form->start()');
        }

        // Value
        $value = '<form id="'.$this->data['name'].'ID" name="'.$this->data['name'].'" method="'.$this->data['method'].'" action="'.$this->data['action'].'" enctype="multipart/form-data" '.$this->onKey.'>';
        $value .= "\n";

        // Return
        return $value;
    }

    // End Form
    final public function end()
    {
        // Value
        $value = '</form>';
        $value .= "\n";

        // Return
        return $value;
    }

    // Dropdown (ie: <select name=""><option></option</select>)
    // $input:  'name'
    final public function dropdown($input,$list,$current_value)
    {
        // Select Name
        $name = isset($input['name']) ? $input['name'] : '';
        if (empty($name))
        {
            error('Dev error: $name is not set for Form->dropdown()');
        }

		// OnClick
		$onclick = 'onclick="sbc_button_sumbit_enable(); document.getElementById(\''.$this->data['submit_id'].'\').disabled = 0; document.getElementById(\''.$this->data['submit_id'].'\').value = \''.$this->data['inactive'].'\';"';

        // Start Select
        $select = '<select id="'.$name.$this->data['name'].'" name="'.$name.'" '.$onclick.'>';
        $select .= "\n";
        $option = ''; // initialize

        // Count Array
        $count  = count($list);
        $key    = array_keys($list);
        $i      = 0;
        while ($i < $count)
        {
            // Values
            $ref        = $key[$i];
            $thevalue   = $list[$ref];

            // Is the current value equal to the dropdown value?
            $is_selected = '';
            if ($thevalue == $current_value)
            {
                $is_selected = ' selected ';
            }

            // Add option
            $option .= '<option value="'.$thevalue.'"'.$is_selected.'>'.$ref.'</option>';
            $option .= "\n";

            $i++;
        }

        // End Select
        $select .= $option;
        $select .= '</select>';
        $select .= "\n";

        // Return
        return $select;
    }

    // Submit
    // $input:  'name', 'css'
    final public function submit($input)
    {
        // Vars
        $name   = isset($input['name']) ? $input['name'] : 'submit';
        $css    = isset($input['css']) ? $input['css'] : '';
        $css    = $this->data['submit_class'] . ' ' . $css;

        // Button
        $button = '<input id="'.$this->data['submit_id'].'" type="Submit" name="'.$name.'" value="'.$this->data['inactive'].'" class="'.$css.'">';
        $button .= "\n";

        // Return
        return $button;
    }

    // Hidden Field
    // $input:  'name', 'value'
    final public function hidden($input)
    {
        // Name
        $name = isset($input['name']) ? $input['name'] : '';
        if (empty($name))
        {
            error('Dev error: $name is not set for Form->hidden()');
        }

        // Value
        $value = isset($input['value']) ? $input['value'] : '';

        // ID
        $id = $name.$this->data['name'];

        // Hidden
        $hidden = '<input id="'.$id.'" name="'.$name.'" type="hidden" value="'.$value.'">';
        $hidden .= "\n";

        // Return
        return $hidden;
    }

    // Input Field
    // $input:  'name', 'type', 'css', 'max', 'value', 
    //          'auto_complete_disable', 'placeholder'
    final public function input($input)
    {
        // CSS
        $css    = isset($input['css']) ? $input['css'] : '';
        $css    = 'input ' . $css;

        // Name
        $name   = isset($input['name']) ? $input['name'] : '';
        if (empty($name))
        {
            error('Dev error: $name is not set for Form->input()');
        }

        // Type
        $type   = isset($input['type']) ? $input['type'] : '';
        if (empty($type))
        {
            error('Dev error: $type is not set for Form->input()');
        }

        // Max
        $max    = isset($input['max']) ? $input['max'] : 0;
        if ($max < 1 || $max > 65535)
        {
            error('Dev error: $max is not set for Form->input()');
        }

        // Value
        $value = isset($input['value']) ? $input['value'] : '';

        // Id
        $id = $name;

        // Disable Autocomplete?
        $auto_complete_disable = isset($input['auto_complete_disable']) ? $input['auto_complete_disable'] : 0;
        $auto_complete = '';
        if ($auto_complete_disable == 1)
        {
            $auto_complete = ' autocomplete="off" ';
        }

        // Placeholder?
        $placeholder = isset($input['placeholder']) ? $input['placeholder'] : '';
        if (!empty($placeholder))
        {
            $placeholder = ' placeholder="'.$placeholder.'" ';
        }

        // Value
        $value = '<input id="'.$id.'" name="'.$name.'" type="'.$type.'" maxlength="'.$max.'" '.$placeholder.' class="'.$css.'" value="'.$value.'" '.$auto_complete.'>';
        $value .= "\n";

        // Return
        return $value;
    }
}