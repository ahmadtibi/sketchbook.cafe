<?php
/**
*
* Message Class for https://www.sketchbook.cafe
*
* @author       Jonathan Maltezo (Kameloh)
* @copyright   (c) 2016, Jonathan Maltezo (Kameloh)
* @lastupdated  2016-04-14
*
*/
class Message
{
    // Constructed
    private $name = '';
    private $min = 0;
    private $column_max = 0; // SQL Column Max Length (VARCHAR)
    private $nl2br = 0;
    private $basic = 0;
    private $ajax = 0;
    private $images = 0;
    private $videos = 0;
    private $ip_address = '';

    // Comment Preview
    private $preview = 0;

    // Generated
    private $id_members = '';

    // Processed
    private $hasinfo = 0;
    private $hasmessage = 0;
    private $message;
    private $message_code;

    // Construct
    public function __construct($input)
    {
        // Name
        $this->name         = isset($input['name']) ? $input['name'] : '';
        if (empty($this->name))
        {
            error('Dev error: $name is not set for Message->construct()');
        }

        // Min Length
        $this->min          = isset($input['min']) ? (int) $input['min'] : 0;
        if ($this->min < 1)
        {
            $this->min = 0;
        }

        // Column Max for SQL
        $this->column_max   = isset($input['column_max']) ? (int) $input['column_max'] : 0;
        if ($this->column_max < 1)
        {
            error('Dev error: $column_max is not set for Message->construct()');
        }

        // New line to <br/>
        $this->nl2br        = isset($input['nl2br']) ? (int) $input['nl2br'] : 0;
        if ($this->nl2br != 1)
        {
            $this->nl2br = 0;
        }

        // Basic HTML and URLs
        $this->basic        = isset($input['basic']) ? (int) $input['basic'] : 0;
        if ($this->basic != 1)
        {
            $this->basic = 0;
        }

        // Ajax
        $this->ajax         = isset($input['ajax']) ? (int) $input['ajax'] : 0;
        if ($this->ajax != 1)
        {
            $this->ajax = 0;
        }

        // Images
        $this->images       = isset($input['images']) ? (int) $input['images'] : 0;
        if ($this->images != 1)
        {
            $this->images = 0;
        }

        // Videos
        $this->videos       = isset($input['videos']) ? (int) $input['videos'] : 0;
        if ($this->videos != 1)
        {
            $this->videos = 0;
        }

        // IP Address
        $this->ip_address = $_SERVER['REMOTE_ADDR'];

        // We got some info!
        $this->hasinfo = 1;
    }

    // Has Info?
    final public function hasInfo()
    {
        if ($this->hasinfo != 1)
        {
            error('Dev error: $hasinfo is not set for Message->hasInfo()');
        }
    }

    // Special Characters Fix : This is a temporary solution
/*
    final private function MessageSpecialChars($input)
    {
        // Arrays
        $s_start	= array (
                '"', 
                "'", 
                '<', 
                '>', 
                '•', 
                '–', 
                '—'
                );
        $s_end	= array (
                '&quot;', 
                '&#039;', 
                '&lt;', 
                '&gt;',
                '&#8226;', 
                '&ndash;',
                '&mdash;'
                );
        $input	= str_replace($s_start, $s_end, $input);

        // Return
        return $input;
    }
*/

    // Check Max Size
    final private function checkMax(&$input)
    {
        if (isset($input{$this->column_max}))
        {
            error('Sorry, '.$this->name.' is too long. Max length is '.$this->column_max.' characters.');
        }
    }

    // Check Min Size
    final private function checkMin(&$input)
    {
        // Set size
        $size = $this->min - 1;

        // Check min
        if (!isset($input{$size}))
        {
            error($this->name.' must be at least '.$this->min.' character(s) long.');
        }
    }

    // Paranoid Replacers
    final private function paranoidReplacers($input)
    {
        // Arrays
        $search     = array
        (
            '"', 
            "'",
            '<?', 
            '?>', 
        );
        $replace    = array
        (
            '&#34;',
            '&#39;', 
            'x<x', 
            'x>x', 
        );
        $input = str_replace($search,$replace,$input);

        // Return
        return $input;
    }

    // Basic HTML and URLs
    final private function basic($input)
    {
        // Arrays
        $search = array
        (
                // <strike>, <b>, <i>, <u>
                '@\&lt;(?i)strike\&gt;(.*?)\&lt;/(?i)strike\&gt;@si',
                '@\&lt;(?i)b\&gt;(.*?)\&lt;/(?i)b\&gt;@si',
                '@\&lt;(?i)i\&gt;(.*?)\&lt;/(?i)i\&gt;@si',
                '@\&lt;(?i)u\&gt;(.*?)\&lt;/(?i)u\&gt;@si',

                // [s], [b], [i], [u]
                '@\[(?i)s\](.*?)\[/(?i)s\]@si',
                '@\[(?i)b\](.*?)\[/(?i)b\]@si',
                '@\[(?i)i\](.*?)\[/(?i)i\]@si',
                '@\[(?i)u\](.*?)\[/(?i)u\]@si',

                // [url=]NAME[/url], [url]URL[/url]
                '@\[(?i)url=(.*?)\](.*?)\[/(?i)url\]@si', 
                '@\[(?i)url\](.*?)\[/(?i)url\]@si', 
        );
        $replace = array
        (
                // <strike>, <b>, <i>, <u>
                '<strike>\\1</strike>',
                '<b>\\1</b>',
                '<i>\\1</i>',
                '<u>\\1</u>',

                // [s], [b], [i], [u]
                '<strike>\\1</strike>',
                '<b>\\1</b>',
                '<i>\\1</i>',
                '<u>\\1</u>',

                // [url=]NAME[/url], [url]URL[/url]
                '<a href="https://www.sketchbook.cafe/out.php?url=\\1">\\2</a>',
                '<a href="https://www.sketchbook.cafe/out.php?url=\\1">\\1</a>',
        );

        // Automatic HTTP URLs
        $input = preg_replace (
            '!([^]:,=]|^)(http://[[:alnum:]\?\$:+_%&amp;=@#/.~-]+[[:alnum:]+_%/-])!i',
            "\\1<a href=\"\\2\" target=\"_new\" tooltip=\"\\2\">\\2</a>",
            $input
        );

        // Automatic HTTPS URLs
        $input = preg_replace (
            '!([^]:,=]|^)(https://[[:alnum:]\?\$:+_%&amp;=@#/.~-]+[[:alnum:]+_%/-])!i',
            "\\1<a href=\"\\2\" target=\"_new\">\\2</a>",
            $input
        );

        // Replace Arrays
        $input = preg_replace($search,$replace,$input);

        // Return
        return $input;
    }

    // Images
    final private function images($input)
    {
        // Arrays
        $search     = array
        (
            '@\[(?i)img\](.*?)\[/(?i)img\]@si',
        );
        $replace    = array
        (
            '<img src=\\1 class=imageBBCode>',
        );

        // Replace
        $input = preg_replace($search,$replace,$input);

        // Return
        return $input;
    }

    // Videos
    final private function videos($input)
    {
        // Arrays
        $search     = array
        (
            // [youtube=VIDEO_ID]
            '@\[(?i)youtube=(.*?)\]@si',
        );
        $replace    = array
        (
            // [youtube=VIDEO_ID]
            '<iframe width=853 height=480 src=https://www.youtube.com/embed/\\1 frameborder=0 allowfullscreen></iframe>',
        );

        // Replace
        $input = preg_replace($search,$replace,$input);

        // Return
        return $input;
    }

    // Insert Message
    final public function insert($input)
    {
        // Strip whitespaces
        $input = trim($input);

        // Check max size
        $this->checkMax($input);

        // Non Ajax
        if ($this->ajax != 1)
        {
            // Replacing + Special Characters
            // $input  = str_replace('\\','',$this->MessageSpecialChars($input,ENT_QUOTES));

        }
        else
        {
           // Replacing and Converting
            $input  = str_replace('\\','',htmlspecialchars($input,ENT_QUOTES));
            $input  = mb_convert_encoding($input,'HTML-ENTITIES','UTF-8');
        }

        // Check min size (only if required)
        if ($this->min > 0)
        {
            $this->checkMin($input);
        }

        // Verify input with PHP's filter
        if (!empty($input))
        {
            if (!filter_var($input,FILTER_SANITIZE_STRING) === true)
            {
                error('Invalid input for '.$this->name);
            }
        }

        // My Paranoid Replacers
        // This is probably not necessary but I like to check these just in case!
        $input = $this->paranoidReplacers($input);

        // Basic HTML and URLs?
        if ($this->basic == 1)
        {
            $input = $this->basic($input);
        }

        // Images
        if ($this->images == 1)
        {
            $input = $this->images($input);
        }

        // Videos
        if ($this->videos == 1)
        {
            $input = $this->videos($input);
        }

        // Double check!
        $input = $this->paranoidReplacers($input);
        $this->checkMax($input);

        // Set message_code for later
        $this->message_code = $input;

        // New line to <br/>
        if ($this->nl2br == 1)
        {
            $input = nl2br($input);
        }

        // Triple Max Check
        $this->checkMax($input);

        // Set Message
        $this->message = $input;

        // Has Message
        $this->hasmessage = 1;
    }

    // Has Message
    final public function hasMessage()
    {
        if ($this->hasmessage != 1)
        {
            error('Dev error: $hasmessage is not set for Message->hasMessage()');
        }
    }

    // Get Message
    final public function getMessage()
    {
        // Check
        $this->hasMessage();

        return $this->message;
    }

    // Get Message Code
    final public function getMessageCode()
    {
        // Check
        $this->hasMessage();

        return $this->message_code;
    }
}