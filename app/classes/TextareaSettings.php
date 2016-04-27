<?php
// @author          Jonathan Maltezo (Kameloh)
// @lastUpdated     2016-04-26
namespace SketchbookCafe\TextareaSettings;

use SketchbookCafe\SBC\SBC as SBC;

class TextareaSettings
{
    public $settings = [];

    // Array
    private $name = '';
    private $min = 0;
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
        $method = 'TextareaSettings->__construct()';

       // Initialize Vars
        $settings = [];

        // Forum Reply
        $settings['forum_reply']    = array
        (
            'name'          => 'message',
            'min'           => 2, // min value
            'max'           => 20000, // user input max
            'column_max'    => 60000, // database max
            'css'           => 'textarea_forum_reply', 
            'preview'       => 1,
            'submit'        => 1,
            'help'          => 1,
            'nl2br'         => 1,
            'basic'         => 1,
            'images'        => 1,
            'videos'        => 1,
            'setting_name'  => 'forum_reply',
        );

        // Forum Thread
        $settings['forum_thread']    = array
        (
            'name'          => 'message',
            'min'           => 2, // min value
            'max'           => 20000, // user input max
            'column_max'    => 60000, // database max
            'css'           => 'textarea_forum_thread', 
            'preview'       => 1,
            'submit'        => 1,
            'help'          => 1,
            'nl2br'         => 1,
            'basic'         => 1,
            'images'        => 1,
            'videos'        => 1,
            'setting_name'  => 'forum_thread',
        );

        // AdminForumForumDescription
        $settings['admin_forum_forum_description']    = array
        (
            'name'          => 'description',
            'max'           => 20000, // user input max
            'column_max'    => 60000, // database max
            'css'           => 'textarea_admin', 
            'preview'       => 1,
            'submit'        => 1,
            'help'          => 1,
            'nl2br'         => 1,
            'basic'         => 1,
            'images'        => 1,
            'videos'        => 1,
            'setting_name'  => 'admin_forum_forum_description',
        );

        // AdminForumCategoryDescription
        $settings['admin_forum_category_description']    = array
        (
            'name'          => 'description',
            'max'           => 20000, // user input max
            'column_max'    => 60000, // database max
            'css'           => 'textarea_admin', 
            'preview'       => 1,
            'submit'        => 1,
            'help'          => 1,
            'nl2br'         => 1,
            'basic'         => 1,
            'images'        => 1,
            'videos'        => 1,
            'setting_name'  => 'admin_forum_category_description',
        );

        // Compose Note for Mailbox
        $settings['composenote']    = array
        (
            'name'          => 'message',
            'min'           => 2, // min value
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

        // Forum Signature
        $settings['forumsignature']    = array
        (
            'name'          => 'forumsignature',
            'max'           => 1000, // user input max
            'column_max'    => 5000, // database max
            'css'           => 'textarea_forumsignature', 
            'preview'       => 1,
            'submit'        => 0,
            'help'          => 1,
            'nl2br'         => 0,
            'basic'         => 1,
            'images'        => 0,
            'videos'        => 0,
            'setting_name'  => 'forumsignature',
        );

        // Note Reply
        $settings['notereply']    = array
        (
            'name'          => 'notereply',
            'min'           => 2, // min value
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
            'setting_name'  => 'notereply',
        );

        // Set Type
        switch ($setting_name)
        {
            case 'forum_reply':         $type = 'forum_reply';
                                        break;

            case 'forum_thread':        $type = 'forum_thread';
                                        break;

            case 'admin_forum_forum_description':
                                        $type = 'admin_forum_forum_description';
                                        break;
        
            case 'admin_forum_category_description':
                                        $type = 'admin_forum_category_description';
                                        break;

            case 'notereply':           $type = 'notereply';
                                        break;

            case 'forumsignature':      $type = 'forumsignature';
                                        break;

            case 'composenote':         $type = 'composenote';
                                        break;

            default:                    $type = '';
                                        break;
        }

        // Type check
        if (empty($type))
        {
            SBC::devError('$setting_name is not set',$method);
        }

        // Optional
        if (isset($settings[$type]['min']))
        {
            $this->min = $settings[$type]['min'];
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
        $method = 'TextareaSettings->setAjax()';

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
        $method = 'TextareaSettings->setValue()';

        $value          = isset($value) ? $value : '';
        $this->value    = $value;
    }

    // Get Settings
    public function getSettings()
    {
        $method = 'TextareaSettings->getSettings()';

        // Set Array
        $settings_array = [];
        $settings_array = array
        (
            'name'          => $this->name,
            'min'           => $this->min,
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