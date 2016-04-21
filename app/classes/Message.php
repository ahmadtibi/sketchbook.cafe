<?php
/**
*
* Message Class for https://www.sketchbook.cafe
*
* @author       Jonathan Maltezo (Kameloh)
* @copyright   (c) 2016, Jonathan Maltezo (Kameloh)
* @lastupdated  2016-04-20
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

    // Master Comment Information
    private $user_id = 0;
    private $parent_id = 0; // for things like forums or mailboxes
    private $comment_type = '';
    private $comment_id = 0;

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
            '<img src="\\1" class="imageBBCode">',
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
            '<iframe width="853" height="480" src="https://www.youtube.com/embed/\\1" frameborder="0" allowfullscreen></iframe>',
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

    // Set User ID
    final public function setUserId($user_id)
    {
        // Initialize Vars
        $user_id = isset($user_id) ? (int) $user_id : 0;
        if ($user_id < 1)
        {
            error('Dev error: $user_id is not set for Message->setUserId()');
        }

        // Set vars
        $this->user_id  = $user_id;
    }

    // Set Comment Type
    final public function setType($type)
    {
        // Initialize vars
        $type = isset($type) ? $type : '';
        if (empty($type))
        {
            error('Dev error: $type is not set for Message->setType()');
        }

        // Switch type
        $comment_type = '';
        switch ($type)
        {
            case 'forum_thread_reply':  $comment_type   = 'forum_thread_reply';
                                        break;

            case 'forum_message':       $comment_type   = 'forum_message';
                                        break;

            case 'note_reply':          $comment_type = 'note_reply';
                                        break;

            case 'new_mail_thread':     $comment_type = 'new_mail_thread';
                                        break;

            default:                    $comment_type = '';
                                        break;
        }

        // Set and check
        $this->comment_type = $comment_type;
        if (empty($this->comment_type))
        {
            error('Dev error: invalid $comment_type for Message->setType()');
        }
    }

    // Get Comment ID
    final public function getCommentId()
    {
        $value = $this->comment_id;
        if ($value < 1)
        {
            error('Dev error: $comment_id is not set for Message->getComment()');
        }
        return $value;
    }

    // Set Parent ID
    final public function setParentId($parent_id)
    {
        $parent_id = isset($parent_id) ? (int) $parent_id : 0;
        if ($parent_id < 1)
        {
            $parent_id = 0;
        }

        // Set
        $this->parent_id = $parent_id;
    }

    // Update Parent ID
    final public function updateParentId(&$db)
    {
        // Initialize Vars
        $comment_id = $this->comment_id;
        $parent_id  = $this->parent_id;

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Update Comment
        $sql = 'UPDATE sbc_comments
            SET parent_id=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ii',$parent_id,$comment_id);
        if (!$stmt->execute())
        {
            error('Could not execute statement (update parent id) for Message->updateParentId()');
        }
        $stmt->close();
    }

    // Create Message
    final public function createMessage(&$db)
    {
        // Check if we have a message
        $this->hasMessage();

        // Classes + Functions
        sbc_function('rd');

        // Initialize Vars
        $rd             = rd();
        $user_id        = $this->user_id;
        $comment_type   = $this->comment_type;
        $time           = time();
        $ip_address     = $_SERVER['REMOTE_ADDR'];
        $message        = $this->message;
        $message_code   = $this->message_code;

        // Make sure a User ID is set
        if ($user_id < 1)
        {
            error('Dev error: $user_id is not set for Message->createMessage()');
        }

        // Make sure we have a comment type
        if (empty($comment_type))
        {
            error('Dev error: $comment_type is not set for Message->createMessage()');
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Insert Comment First!
        $sql = 'INSERT INTO sbc_comments
            SET rd=?,
            user_id=?,
            date_created=?,
            ip_created=?,
            message=?,
            message_code=?,
            isdeleted=1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('iiisss',$rd,$user_id,$time,$ip_address,$message,$message_code);
        if (!$stmt->execute())
        {
            error('Could not execute statement (insert comment) for Message->createMessage()');
        }
        $stmt->close();

        // Get Comment ID
        $sql = 'SELECT id
            FROM sbc_comments
            WHERE rd=?
            AND user_id=?
            AND date_created=?
            AND isdeleted=1
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('iii',$rd,$user_id,$time);
        if (!$stmt->execute())
        {
            error('Could not execute statement (get comment ID) for Message->createMessage()');
        }
        $result = $stmt->get_result();
        $row    = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        $stmt->close();

        // Comment ID?
        $comment_id = isset($row['id']) ? (int) $row['id'] : 0;
        if ($comment_id < 1)
        {
            error('Dev error: could not get new comment_id for Message->createMessage()');
        }
        $this->comment_id = $comment_id;

        // Forum Thread Reply (type 3)
        if ($comment_type == 'forum_thread_reply')
        {
            // Update comment and mark as undeleted
            $sql = 'UPDATE sbc_comments
                SET type=3,
                isdeleted=0
                WHERE id=?
                LIMIT 1';
            $stmt = $db->prepare($sql);
            $stmt->bind_param('i',$comment_id);
            if (!$stmt->execute())
            {
                error('Could not execute statement (update comment as forum reply) for Message->createMessage()');
            }
            $stmt->close();
        }
        // Forum Message (type 2)
        else if ($comment_type == 'forum_message')
        {
            // Update comment and mark as undeleted
            $sql = 'UPDATE sbc_comments
                SET type=2,
                isdeleted=0
                WHERE id=?
                LIMIT 1';
            $stmt = $db->prepare($sql);
            $stmt->bind_param('i',$comment_id);
            if (!$stmt->execute())
            {
                error('Could not execute statement (update comment as forum message) for Message->createMessage()');
            }
            $stmt->close();
        }
        // Mail Threads (type 1)
        else if ($comment_type == 'new_mail_thread')
        {
            // Update comment + Mark as undeleted
            $sql = 'UPDATE sbc_comments
                SET type=1,
                ismail=1,
                isprivate=1,
                isdeleted=0
                WHERE id=?
                LIMIT 1';
            $stmt = $db->prepare($sql);
            $stmt->bind_param('i',$comment_id);
            if (!$stmt->execute())
            {
                error('Could not execute statement (update comment as mail thread) for Message->createMessage()');
            }
            $stmt->close();
        }
        // Note reply (type 1)
        else if ($comment_type == 'note_reply')
        {
            // Update comment + Mark as undeleted
            $sql = 'UPDATE sbc_comments
                SET type=1,
                ismail=1,
                isprivate=1,
                isdeleted=0
                WHERE id=?
                LIMIT 1';
            $stmt = $db->prepare($sql);
            $stmt->bind_param('i',$comment_id);
            if (!$stmt->execute())
            {
                error('Could not execute statement (update comment as mail thread) for Message->createMessage()');
            }
            $stmt->close();
        }
        else
        {
            error('Dev error: something went wrong... (invalid $comment_type) for Message->createMessage()');
        }
    }
}