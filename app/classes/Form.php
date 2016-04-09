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
        $value = '<form id="'.$this->data['name'].'ID" name="'.$this->data['name'].'" method="'.$this->data['method'].'" action="'.$this->data['action'].'" enctype="multipart/form-data" '.$this->onKey.' accept-charset="UTF-8">';
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
    // $input:  'name', 'css'
    final public function dropdown($input,$list,$current_value)
    {
        // Select Name
        $name = isset($input['name']) ? $input['name'] : '';
        if (empty($name))
        {
            error('Dev error: $name is not set for Form->dropdown()');
        }

        // CSS
        $css = isset($input['css']) ? $input['css'] : '';
        $css .= ' dropdown ';

		// OnClick
		$onclick = 'onclick="sbc_button_sumbit_enable(); document.getElementById(\''.$this->data['submit_id'].'\').disabled = 0; document.getElementById(\''.$this->data['submit_id'].'\').value = \''.$this->data['inactive'].'\';"';

        // Start Select
        $select = '<select id="'.$name.$this->data['name'].'" name="'.$name.'" class="'.$css.'" '.$onclick.'>';
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

    // Checkbox
    // $input   'name', 'value', 'checked', 'css'
    final public function checkbox($input)
    {
        // Name
        $name = isset($input['name']) ? $input['name'] : '';
        if (empty($name))
        {
            error('Dev error: $name is not set for $Form->checkbox()');
        }

        // CSS
        $css = isset($input['css']) ? $input['css'] : '';
        $css .= ' checkbox ';

        // Checked
        $checked        = isset($input['checked']) ? $input['checked'] : 0;
        $checked_text   = '';
        if ($checked == 1)
        {
            $checked_text = ' checked ';
        }

        // Value
        $value = isset($input['value']) ? $input['value'] : 0;

		// OnClick
		$onclick = 'onclick="sbc_button_sumbit_enable(); document.getElementById(\''.$this->data['submit_id'].'\').disabled = 0; document.getElementById(\''.$this->data['submit_id'].'\').value = \''.$this->data['inactive'].'\';"';

        // Checkbox
        $checkbox = '<input id="'.$name.$this->data['name'].'" name="'.$name.'" type="checkbox" value="'.$value.'" class="'.$css.'" '.$checked_text.' '.$onclick.'>';
        $checkbox .= "\n";

        // Return
        return $checkbox;
    }

    // Textarea
    final public function textarea($input)
    {
        // Name
        $name = isset($input['name']) ? $input['name'] : '';
        if (empty($name))
        {
            error('Dev error: $name is not set for Form->textarea()');
        }

        // Value
        $value = isset($input['value']) ? $input['value'] : '';

        // Placeholder
        $placeholder = isset($input['placeholder']) ? $input['placeholder'] : '';

        // ID
        $id = 'textarea_'.$this->data['name'].'_'.$name;

        // CSS
        $css = isset($input['css']) ? $input['css'] : '';
        $css .= ' textarea ';

        // Max
        $max    = isset($input['max']) ? (int) $input['max'] : 0;
        if ($max < 1 || $max > 30000)
        {
            error('Dev error: $max is not set for Form->textarea()');
        }

        // Vars
        $textarea       = '';
        $preview_name   = $id.'_preview';
        $help_top       = ''; // links
        $help_bottom    = ''; // description + examples
        $help_all       = '';

        // New Line to BR
        $nl2br  = isset($input['nl2br']) ? (int) $input['nl2br'] : 0;
        if ($nl2br != 1)
        {
            $nl2br = 0;
        }

        // Basic HTML, BBCode and URLs
        $basic = isset($input['basic']) ? (int) $input['basic'] : 0;
        if ($basic != 1)
        {
            $basic = 0;
        }

        // Image BBCode
        $imagebbcode = isset($input['imagebbcode']) ? (int) $input['imagebbcode'] : 0;
        if ($imagebbcode != 1)
        {
            $imagebbcode = 0;
        }

        // Video
        $video  = isset($input['video']) ? (int) $input['video'] : 0;
        if ($video != 1)
        {
            $video = 0;
        }

        // (FIXME)
        $setting_name = '';

        // Preview
        $preview        = isset($input['preview']) ? (int) $input['preview'] : 0;
        $preview_button = '';
        if ($preview == 1)
        {
            $preview_id     = 'textarea_'.$this->data['name'].'_'.$name;
            $preview_button = '<button type="button" onClick="comment_preview(\''.$preview_id.'\', \''.$setting_name.'\'); return false;" class="input">Preview</button>';
        }
        $this->preview[$name] = $preview;

        // Help Document
        $help   = isset($input['help']) ? (int) $input['help'] : 0;
        if ($help != 1)
        {
            $help = 0;
        }

        // Submit
        $submit         = isset($input['submit']) ? (int) $input['submit'] : 0;
        $submit_value   = '';
        if ($submit == 1)
        {
            $submit_value = $this->submit(array
            (
                'name'  => 'submit',
                'css'   => '', 
            ));
        }

        // Preview
        $textarea .= '<div id="'.$preview_name.'"></div>';

        // Help Document (FIXME)
        if ($help == 1)
        {

            // Combine
        }

        // Textarea
        $textarea .= '<textarea name="'.$name.'" id="'.$id.'" class="'.$css.'" maxlength="'.$max.'" placeholder="'.$placeholder.'">'.$value.'</textarea>';

        // New Textarea (not added)
        $textarea = '<div>' . $textarea . '</div>';
        $textarea .= '<div>' . $preview_button . ' ' . $submit_value . '</div>';

        // Return
        return $textarea;
    }
}