<?php
/**
*
* ImageFile Class
*
* @author       Jonathan Maltezo (Kameloh)
* @copyright    (c) 2016, Jonathan Maltezo (Kameloh)
* @lastupdated  2016-04-12
*
*/

class ImageFile
{
    public $name = '';
    private $user_id = 0;
    private $rd = 0;
    private $rd_code = '';
    private $ip_address = '';
    private $date_created = 0;

    // Settings
    private $max_filesize = 0;
    private $required = 0;
    private $allow_gif = 0;
    private $allow_png = 0;
    private $allow_jpg = 0;
    private $allow_apng = 0;
    private $width_min = 0;
    private $width_max = 0;
    private $height_min = 0;
    private $height_max = 0;
    private $hasinfo = 0;
    private $isready = 0;

    // Sending File
    private $buffer;
    public $file; // linked

    // File Info
    private $filetype = '';
    private $filesize = 0;
    private $hasfile = 0;

    // SQL Stuff
    private $old_id = 0;
    private $image_id = 0;
    private $image_url = '';

    // Construct
    // $input   'name', 'user_id', 'max_filesize', 'required', 'allow_gif', 'allow_png', 'allow_jpg', 
    //          'allow_apng', 'width_min', 'width_max', 'height_min', 'height_max'
    public function __construct($input)
    {
        // Functions
        sbc_function('generate_random');

        // Set Vars
        $this->ip_address   = $_SERVER['REMOTE_ADDR'];
        $this->rd           = rand(100000,9999999);
        $this->rd_code      = generate_random(5);

        // Name
        $this->name = isset($input['name']) ? $input['name'] : '';
        if (empty($this->name))
        {
            error('Dev error: $name is not set for ImageFile->construct()');
        }

        // User ID
        $this->user_id = isset($input['user_id']) ? (int) $input['user_id'] : 0;
        if ($this->user_id < 1)
        {
            $this->user_id = 0;
        }

        // File Size
        $this->max_filesize = isset($input['max_filesize']) ? (int) $input['max_filesize'] : 0;
        if ($this->max_filesize < 1 || $this->max_filesize > 20971520)
        {
            error('Dev error: $max_filesize is not set for ImageFile->construct()');
        }

        // Required
        $this->required = isset($input['required']) ? (int) $input['required'] : 0;
        if ($this->required != 1)
        {
            $this->required = 0;
        }

        // Allowed Filetypes
        $this->allow_png    = isset($input['allow_png']) ? (int) $input['allow_png'] : 0;
        $this->allow_gif    = isset($input['allow_gif']) ? (int) $input['allow_gif'] : 0;
        $this->allow_jpg    = isset($input['allow_jpg']) ? (int) $input['allow_jpg'] : 0;
        $this->allow_apng   = isset($input['allow_apng']) ? (int) $input['allow_apng'] : 0;

        // Image Dimensions
        $this->width_min    = isset($input['width_min']) ? (int) $input['width_min'] : 0;
        $this->width_max    = isset($input['width_max']) ? (int) $input['width_max'] : 0;
        $this->height_min   = isset($input['height_min']) ? (int) $input['height_min'] : 0;
        $this->height_max   = isset($input['height_max']) ? (int) $input['height_max'] : 0;

        // Check Width
        if ($this->width_min < 1)
        {
            $this->width_min = 0;
        }
        if ($this->width_max < 1 || $this->width_max > 10000)
        {
            error('Dev error: $width_max must be between 1 and 10000 for ImageFile->construct()');
        }

        // Check Height
        if ($this->height_min < 1)
        {
            $this->height_min = 0;
        }
        if ($this->height_max < 1 || $this->height_max > 10000)
        {
            error('Dev error: $height_max must be between 1 and 10000 for ImageFile->construct()');
        }

        // Has Info
        $this->hasinfo = 1;
    }

    // Identify APNG
    private function identifyAPNG(&$data)
    {
        // Initialize Vars
        $value = 0;

        // Data?
        if ($data)
        {
            if (strpos(substr($data,0,strpos($data,'IDAT')),'acTL') !== false)
            {
                $value = 1;
            }
        }

        // Return value
        return $value;
    }

    // Check File Type
    private function checkFileType(&$data)
    {
        // Initialize Vars
        $type = '';

        // Check data type
        if (substr($data,0,8) == "\x89PNG\x0D\x0A\x1A\x0A")
        {
            $type = 'png';
        }
        else if (substr ($data, 0, 2) == "\xFF\xD8")
        {
            $type = 'jpg';
        }
        else if (substr ($data, 0, 3) == 'GIF')
        {
            $type = 'gif';
        }

        // Return type
        return $type;
    }

    // Send File
    final public function sendFile()
    {
        // Has Info?
        $this->hasInfo();

        // File
        $file   = isset($_FILES['imagefile']['tmp_name']) ? $_FILES['imagefile']['tmp_name'] : '';

        // Buffer
        $this->buffer = @file_get_contents($file);

        // Link (might remove this later)
        $this->file = $file;

        // Check Filesize
        $this->filesize = strlen($this->buffer);
        if ($this->filesize > $this->max_filesize)
        {
            error('Sorry, the filesize is too large. Max filesize is '.$this->max_filesize.' bytes.');
        }

        // Empty buffer?
        if (empty($this->buffer))
        {
            // Is the file required?
            if ($this->required == 1)
            {
                error('Please select a file to upload.');
            }
        }
        else
        {
            // Get Filetype
            $this->filetype = $this->checkFileType($this->buffer);

            // Check if the filetype is allowed
            $allowed_filetype = 0;

            // PNGs
            if ($this->filetype == 'png' && $this->allow_png == 1)
            {
                $allowed_filetype = 1;
            }

            // JPGs
            if ($this->filetype == 'jpg' && $this->allow_jpg == 1)
            {
                $allowed_filetype = 1;
            }

            // GIFs
            if ($this->filetype == 'gif' && $this->allow_gif == 1)
            {
                $allowed_filetype = 1;
            }

            // Allowed?
            if ($allowed_filetype != 1)
            {
                error('Sorry, this filetype is not allowed for uploads.');
            }

            // Animated PNGs
            if ($this->allow_apng != 1 && $this->filetype == 'png')
            {
                // Check
                $apng_check = $this->identifyAPNG($this->buffer);
                if ($apng_check == 1)
                {
                    error('Sorry, animated PNGs are not allowed for uploads.');
                }
            }

            // Check if the image can be read
            $imagesize = @getimagesize($file);
            if (!$imagesize)
            {
                error('Image cannot be read. File may be corrupt.');
            }

            // Set image width and height
            $this->image_width  = $imagesize[0];
            $this->image_height = $imagesize[1];

            // Width Check
            if ($this->image_width < $this->width_min)
            {
                error('Minimum image width must be at least '.$this->width_min.'px');
            }
            if ($this->image_width > $this->width_max)
            {
                error('Maximum image width is '.$this->width_max.'px (width is '.$this->image_width.'px)');
            }

            // Height Check
            if ($this->image_height < $this->height_min)
            {
                error('Minimum image height must be at least '.$this->height_min.'px');
            }
            if ($this->image_height > $this->height_max)
            {
                error('Maximum image height is '.$this->height_max.'px (height is '.$this->image_height.'px)');
            }

            // Set has file
            $this->hasfile = 1;
        }
    }

    // Has Info
    final public function hasInfo()
    {
        if ($this->hasinfo != 1)
        {
            error('Information is not set for ImageFile->hasInfo()');
        }
    }

    // Has File
    final public function hasFile()
    {
        // True?
        if ($this->hasfile == 1)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    // Set User ID
    final public function setUserId($user_id)
    {
        // Has info?
        $this->hasInfo();

        // Check
        $user_id = isset($user_id) ? (int) $user_id : 0;
        if ($user_id < 1)
        {
            error('Dev error: $user_id is not set for ImageFile->setUserId()');
        }

        // Set
        $this->user_id = 1;
        $this->isready = 1;
    }

    // Get Info
    final public function getInfo()
    {
        // Make sure we have the file
        $this->hasFile();

        // Initialize Vars
        $value = array
        (
            'rd'        => $this->rd,
            'rd_code'   => $this->rd_code,
            'filetype'  => $this->filetype,
            'filesize'  => $this->filesize,
        );

        // Return array
        return $value;
    }
}