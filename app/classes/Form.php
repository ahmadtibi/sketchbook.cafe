<?php
/**
*
* Form Class
* 
* @author       Kameloh
* @copyright    (c) 2016, Kameloh
* @lastUpdated  2016-05-11
*
*/
namespace SketchbookCafe\Form;

use SketchbookCafe\SBC\SBC as SBC;

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

    // CSS
    private $css_input = ' sbc_font ';
    private $css_dropdown = ' sbc_font ';
    private $css_textarea = ' sbc_font sbc_font_size sbc_font_height ';
    private $css_formHelpTd = ' formHelpTd sbc_font ';
    private $css_formEnabledItem = ' formEnabledItem sbc_font ';

    // Construct
    public function __construct($input)
    {
        $method = 'Form->__construct()';

        // Initialize Vars
        $this->data['test']     = 1;
        $this->field['test']    = 1;

        // Name
        if (empty($input['name']))
        {
            SBC::devError('$name is not set',$method);
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
        $this->onSubmit     = 'formOnSubmit (\''.$this->data['submit_id'].'\', \''.$this->data['active'].'\'); sbc_button_sumbit_disable();';
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

    // Set Javascript
    final public function setJavascript($javascript)
    {
        $method = 'Form->setJavascript()';

        // Set?
        $javascript = isset($javascript) ? $javascript : '';
        if (!empty($javascript))
        {
            $this->data['ajax']         = 1;
            $this->data['javascript']   = $javascript;

            // On Submit
            $this->onSubmit .= ' '.$javascript.' return false; ';
        }
    }

    // Start Form
    final public function start()
    {
        $method = 'Form->start()';

        // Make sure this form has base information
        if ($this->hasinfo != 1)
        {
            SBC::devError('$hasinfo is not set',$method);
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
        $method = 'Form->end()';

        // Value
        $value = '</form>';
        $value .= "\n";

        // Return
        return $value;
    }


    // Stylized Uploads
    // $input:  'name', 'imagefile', 'post_url', 'css'
    final public function upload($input)
    {
        $method = 'Form->upload()';

        // Initialize Vars
        $name       = isset($input['name']) ? $input['name'] : '';
        $imagefile  = isset($input['imagefile']) ? $input['imagefile'] : '';
        $post_url   = isset($input['post_url']) ? $input['post_url'] : '';
        $css        = isset($input['css']) ? $input['css'] : '';
        $css        = $this->data['submit_class'].' '.$css;

        // Make sure inputs are correct
        if (empty($name) || empty($imagefile) || empty($post_url))
        {
            SBC::devError('a required variable is empty: name:'.$name.', imagefile:'.$imagefile.', post_url:'.$post_url,$method);
        }

        // Extra
        $div_id     = $name.'_upload';

        // Value (clean this later!)
        $value = '
<input type="button" value="Upload" class="'.$css.'" onclick="sbc_upload_file(\''.$imagefile.'\',\''.$post_url.'\'); sbc_button_sumbit_disable();">
<div id="'.$div_id.'" style="display: none;">
<progress id="progressBar" class="uploadProgressBar" value="0" max="100"></progress>
<h3 id="status" class="uploadStatus"></h3>
<p id="loaded_n_total" class="uploadBytes"></p>
</div>
';

        // Return
        return $value;
    }

    // File Input (basic input)
    // $input:  'name'
    final public function file($input)
    {
        $method = 'Form->file()';

        // Initialize Form
        $name   = isset($input['name']) ? $input['name'] : '';
        $id     = $name;

        // Check
        if (empty($name))
        {
            SBC::devError('$name is not set',$method);
        }

        // onClick
        $onclick = 'onclick="sbc_button_sumbit_enable(); document.getElementById(\''.$this->data['submit_id'].'\').disabled = 0; document.getElementById(\''.$this->data['submit_id'].'\').value = \''.$this->data['inactive'].'\';"';

        // Value
        $value = '<input name="'.$name.'" id="'.$id.'" type="file" '.$onclick.'>';

        // Return
        return $value;
    }

    // Dropdown (ie: <select name=""><option></option</select>)
    // $input:  'name', 'css'
    final public function dropdown($input,$list,$current_value)
    {
        $method = 'Form->dropdown()';

        // Select Name
        $name = isset($input['name']) ? $input['name'] : '';
        if (empty($name))
        {
            SBC::devError('$name is not set',$method);
        }

        // CSS
        $css = isset($input['css']) ? $input['css'] : '';
        $css .= ' dropdown '.$this->css_dropdown;

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
    // $input:  'name', 'css', 'value'
    final public function submit($input)
    {
        $method = 'Form->submit()';

        // Vars
        $name   = isset($input['name']) ? $input['name'] : 'submit';
        $css    = isset($input['css']) ? $input['css'] : '';
        $css    = $this->data['submit_class'] . ' ' . $css;
        $value  = isset($input['value']) ? $input['value'] : '';

        // Do we have a value?
        if (empty($value))
        {
            $value = $this->data['inactive'];
        }

        // Ajax?
        if ($this->data['ajax'] == 1)
        {
            $css = $this->data['button_class'] . ' ' . $css;

            // Ajax Button
            $button = '<button id="'.$this->data['submit_id'].'" type="button" name="'.$name.'" value="'.$value.'" class="'.$css.'" onclick="'.$this->data['javascript'].'" >'.$value.'</button>';
        }
        else
        {
            // Button
            $button = '<input id="'.$this->data['submit_id'].'" type="Submit" name="'.$name.'" value="'.$value.'" class="'.$css.'">';
        }

        // Line Spacer
        $button .= "\n";

        // Return
        return $button;
    }

    // Hidden Field
    // $input:  'name', 'value'
    final public function hidden($input)
    {
        $method = 'Form->hidden()';

        // Name
        $name = isset($input['name']) ? $input['name'] : '';
        if (empty($name))
        {
            SBC::devError('$name is not set',$method);
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
        $method = 'Form->input()';

        // CSS
        $css    = isset($input['css']) ? $input['css'] : '';
        $css    = 'input ' . $css . $this->css_input;

        // Name
        $name   = isset($input['name']) ? $input['name'] : '';
        if (empty($name))
        {
            SBC::devError('$name is not set',$method);
        }

        // Type
        $type   = isset($input['type']) ? $input['type'] : '';
        if (empty($type))
        {
            SBC::devError('$type is not set',$method);
        }

        // Max
        $max    = isset($input['max']) ? $input['max'] : 0;
        if ($max < 1 || $max > 65535)
        {
            SBC::devError('$max is not set',$method);
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
        $method = 'Form->checkbox()';

        // Name
        $name = isset($input['name']) ? $input['name'] : '';
        if (empty($name))
        {
            SBC::devError('$name is not set',$method);
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
    // $input   'name', 'value', 'placeholder', 'max', 'css', 'preview'
    //          'help', 'submit', 'nl2br', 'basic', 'images', 'videos'
    //          'setting_name'
    final public function textarea($input)
    {
        $method = 'Form->textarea()';

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
        $css .= ' textarea '.$this->css_textarea;

        // Max
        $max    = isset($input['max']) ? (int) $input['max'] : 0;
        if ($max < 1 || $max > 30000)
        {
            SBC::devError('$max is not set',$method);
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

        // Images
        $images = isset($input['images']) ? (int) $input['images'] : 0;
        if ($images != 1)
        {
            $images = 0;
        }

        // Videos
        $videos = isset($input['videos']) ? (int) $input['videos'] : 0;
        if ($videos != 1)
        {
            $videos = 0;
        }

        // Setting Name (for message previewer)
        $setting_name = isset($input['setting_name']) ? $input['setting_name'] : '';

        // Preview
        $preview        = isset($input['preview']) ? (int) $input['preview'] : 0;
        $preview_button = '';
        if ($preview == 1)
        {
            $preview_id     = 'textarea_'.$this->data['name'].'_'.$name;
            $preview_button = '<button type="button" class="button" onClick="comment_preview(\''.$preview_id.'\', \''.$setting_name.'\'); return false;" class="input">Preview</button>';
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
            // Basic HTML
            if ($basic == 1)
            {
                $temp = $id . '_basicHTML';
                $help_top .= '<span class="'.$this->css_formEnabledItem.'"><a href="" onClick="hideshow(\'' . $temp . '\'); return false;">Basic HTML</a></span>';
                $help_bottom .= '
<!-- Start Basic HTML -->
<span id="' . $temp . '" style="display: none;">
    <div class="formHelpOverlay">


        <div class="formHelpContent">

            <div class="table">
                <div class="tr">
                    <div class="td fb '.$this->css_formHelpTd.'">
                        Code
                    </div>
                    <div class="td fb '.$this->css_formHelpTd.'">
                        Example
                    </div>
                    <div class="td fb '.$this->css_formHelpTd.'">
                        Output
                    </div>
                </div>

                <div class="tr">
                    <div class="td '.$this->css_formHelpTd.'">
                        &lt;b&gt;&lt;/b&gt;
                    </div>
                    <div class="td '.$this->css_formHelpTd.'">
                        &lt;b&gt;this is a message&lt;/b&gt;
                    </div>
                    <div class="td '.$this->css_formHelpTd.'">
                        <b>this is a message</b>
                    </div>
                </div>

                <div class="tr">
                    <div class="td '.$this->css_formHelpTd.'">
                        &lt;i&gt;&lt;/i&gt;
                    </div>
                    <div class="td '.$this->css_formHelpTd.'">
                        &lt;i&gt;This message uses italics&lt;/i&gt;
                    </div>
                    <div class="td '.$this->css_formHelpTd.'">
                        <i>This message uses italics</i>
                    </div>
                </div>

                <div class="tr">
                    <div class="td '.$this->css_formHelpTd.'">
                        &lt;u&gt;&lt;/u&gt;
                    </div>
                    <div class="td '.$this->css_formHelpTd.'">
                        Sometimes it\'s &lt;u&gt;important&lt;/u&gt; to underline things
                    </div>
                    <div class="td '.$this->css_formHelpTd.'">
                        Sometimes it\'s <u>important</u> to underline things
                    </div>
                </div>

                <div class="tr">
                    <div class="td '.$this->css_formHelpTd.'">
                        &lt;strike&gt;&lt;/strike&gt;
                    </div>
                    <div class="td '.$this->css_formHelpTd.'">
                        This is &lt;strike&gt;an example of&lt;/strike&gt; a strikethrough
                    </div>
                    <div class="td '.$this->css_formHelpTd.'">
                        This is <strike>an example of</strike> a strikethrough
                    </div>
                </div>

            </div>
        </div>
    </div>
</span>
<!-- End Basic HTML -->
';

                // URLs
                $temp = $id . '_urls';
                $help_top .= '<span class="'.$this->css_formEnabledItem.'"><a href="" onClick="hideshow(\'' . $temp . '\'); return false;">URLs</a></span>';
                $help_bottom .= '
<!-- Start URLs -->
<span id="' . $temp . '" style="display: none;">
    <div class="formHelpOverlay">


        <div class="formHelpContent">

            <div class="table">
                <div class="tr">
                    <div class="td fb '.$this->css_formHelpTd.'">
                        Example
                    </div>
                    <div class="td fb '.$this->css_formHelpTd.'">
                        Output
                    </div>
                </div>

                <div class="tr">
                    <div class="td '.$this->css_formHelpTd.'">
                        http://www.google.com
                    </div>
                    <div class="td '.$this->css_formHelpTd.'">
                        <a href="http://www.google.com" target="_new">http://www.google.com</a>
                    </div>
                </div>

                <div class="tr">
                    <div class="td '.$this->css_formHelpTd.'">
                        [url]http://www.google.com[/url]
                    </div>
                    <div class="td '.$this->css_formHelpTd.'">
                        <a href="http://www.google.com" target="_new">http://www.google.com</a>
                    </div>
                </div>

                <div class="tr">
                    <div class="td '.$this->css_formHelpTd.'">
                        :userKameloh:
                    </div>
                    <div class="td '.$this->css_formHelpTd.'">
                        <a href="#" target="_new">userKameloh</a>
                    </div>
                </div>


            </div>
        </div>
    </div>
</span>
<!-- End URLs -->
';
            }

            // Image BBCode
            if ($images == 1)
            {
                $temp = $id . '_imgbbcode';
                $help_top .= '<span class="'.$this->css_formEnabledItem.'"><a href="" onClick="hideshow(\'' . $temp . '\'); return false;">Images</a></span>';
                $help_bottom .= '
<!-- Start Images -->
<span id="' . $temp . '" style="display: none;">
    <div class="formHelpOverlay">


        <div class="formHelpContent">

            <div class="table">
                <div class="tr">
                    <div class="td fb '.$this->css_formHelpTd.'">
                        Example
                    </div>
                    <div class="td fb '.$this->css_formHelpTd.'">
                        Output
                    </div>
                </div>

                 <div class="tr">
                    <div class="td '.$this->css_formHelpTd.'">
                        [img]http://url.of.image[/img]
                    </div>
                    <div class="td '.$this->css_formHelpTd.'">
                        (should display the image in your post)
                    </div>
                </div>

            </div>
        </div>
    </div>
</span>
<!-- End Images -->
';
            }

            // Videos
            if ($videos == 1)
            {
                $temp = $id . '_video';
                $help_top .= '<span class="'.$this->css_formEnabledItem.'"><a href="" onClick="hideshow(\'' . $temp . '\'); return false;">Video</a></span>';
                $help_bottom .= '
<!-- Start Video -->
<span id="' . $temp . '" style="display: none;">
    <div class="formHelpOverlay">


        <div class="formHelpContent">

            <div class="table">
                <div class="tr">
                    <div class="td fb '.$this->css_formHelpTd.'">
                        Example
                    </div>
                    <div class="td fb '.$this->css_formHelpTd.'">
                        Output
                    </div>
                </div>

                 <div class="tr">
                    <div class="td '.$this->css_formHelpTd.'">
                        [youtube=VIDEO_ID]
                    </div>
                    <div class="td '.$this->css_formHelpTd.'">
                        (displays youtube video)
                    </div>
                </div>

            </div>
        </div>
    </div>
</span>
<!-- End Video -->
';
            }

            // Combine
            $textarea .= '<div class="formEnabledOverlay">' . $help_top.$help_bottom . '</div>';
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