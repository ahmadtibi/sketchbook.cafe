<?php
// Textarea Settings Class

class TextareaSettings
{
    public $settings = [];

    // Array
    private $name = '';
    private $max = 0;
    private $column_max = 0;
    private $css = '';
    private $preview = 0;
    private $submit = 0;
    private $help = 0;
    private $nl2br = 0;
    private $basic =0 ;
    private $images = 0;
    private $videos = 0;
    private $setting_name = '';

    // User Input
    private $value = '';
    private $ajax = 0;

    // Construct
    public function __construct($setting_name)
    {
       // Initialize Vars
        $settings = [];

        // Compose Note for Mailbox
        $settings['composenote']    = array
        (
            'name'          => 'message',
            'max'           => 20000, // user input max
            'column_max'    => 60000, // database max
            'css'           => 'textarea_compose', 
            'preview'       => 1,
            'submit'        => 1,
            'help'          => 1,
            'nl2br'         => 1,
            'basic'         => 1,
            'images'        => 1,
            'videos'        => 1,
            'setting_name'  => 'composenote',
        );

        // Set Type
        switch ($setting_name)
        {
            case 'composenote':         $type = 'composenote';
                                        break;

            default:                    $type = '';
                                        break;
        }

        // Type check
        if (empty($type))
        {
            error('Dev error: $setting_name is not set for TextareaSettings->getSettings()');
        }

        // Set vars
        $this->name         = $settings[$type]['name'];
        $this->max          = $settings[$type]['max'];
        $this->column_max   = $settings[$type]['column_max'];
        $this->css          = $settings[$type]['css'];
        $this->preview      = $settings[$type]['preview'];
        $this->submit       = $settings[$type]['submit'];
        $this->help         = $settings[$type]['help'];
        $this->nl2br        = $settings[$type]['nl2br'];
        $this->basic        = $settings[$type]['basic'];
        $this->images       = $settings[$type]['images'];
        $this->videos       = $settings[$type]['videos'];
        $this->setting_name = $settings[$type]['setting_name'];
    }

    // Set Ajax
    public function setAjax($value)
    {
        $value = isset($value) ? (int) $value : 0;
        if ($value != 1)
        {
            $value = 0;
        }

        // Set
        $this->ajax = $value;
    }

    // Set Value
    public function setValue($value)
    {
        $value          = isset($value) ? $value : '';
        $this->value    = $value;
    }

    // Get Settings
    public function getSettings()
    {
        // Set Array
        $settings_array = [];
        $settings_array = array
        (
            'name'          => $this->name,
            'max'           => $this->max, // user input max
            'column_max'    => $this->column_max, // database max
            'css'           => $this->css, 
            'preview'       => $this->preview,
            'submit'        => $this->submit,
            'help'          => $this->help,
            'nl2br'         => $this->nl2br,
            'basic'         => $this->basic,
            'images'        => $this->images,
            'videos'        => $this->videos,
            'setting_name'  => $this->setting_name,

            'value'         => $this->value,
            'ajax'          => $this->ajax,
        );
 
        // Return array
        return $settings_array;
    }
}