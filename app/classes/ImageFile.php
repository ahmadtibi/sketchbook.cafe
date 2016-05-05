<?php
/**
*
* ImageFile Class
*
* @author       Kameloh
* @copyright    (c) 2016, Kameloh
* @lastupdated  2016-05-04
*
*/
namespace SketchbookCafe\ImageFile;

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\GenerateRandom\GenerateRandom as GenerateRandom;

class ImageFile
{
    public $name = '';
    private $time = 0;
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
    private $file_url = '';
    private $thumb_name = ''; // does not contain filetype

    // SQL Stuff
    private $old_id = 0;
    private $image_id = 0;
    private $image_url = '';

    // Construct
    // $input   'name', 'user_id', 'max_filesize', 'required', 'allow_gif', 'allow_png', 'allow_jpg', 
    //          'allow_apng', 'width_min', 'width_max', 'height_min', 'height_max'
    public function __construct($input)
    {
        $method = 'ImageFile->__construct()';

        // Set Vars
        $this->time         = SBC::getTime();
        $this->ip_address   = SBC::getIpAddress();
        $this->rd           = SBC::rd();
        // $randObj            = new GenerateRandom(5);
        $this->rd_code      = GenerateRandom::process(5);
        // $this->rd_code      = $randObj->getValue();

        // Name
        $this->name = isset($input['name']) ? $input['name'] : '';
        if (empty($this->name))
        {
            SBC::devError('$name is not set',$method);
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
            SBC::devError('$max_filesize is not set',$method);
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
            SBC::devError('$width_max must be between 1 and 10000',$method);
        }

        // Check Height
        if ($this->height_min < 1)
        {
            $this->height_min = 0;
        }
        if ($this->height_max < 1 || $this->height_max > 10000)
        {
            SBC::devError('$height_max must be between 1 and 10000',$method);
        }

        // Has Info
        $this->hasinfo = 1;
    }

    // Identify APNG
    private function identifyAPNG(&$data)
    {
        $method = 'ImageFile->identifyAPNG()';

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
        $method = 'ImageFile->checkFileType';

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
        $method = 'ImageFile->sendFile()';

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
            SBC::userError('Sorry, the filesize is too large. Max filesize is '.$this->max_filesize.' bytes.');
        }

        // Empty buffer?
        if (empty($this->buffer))
        {
            // Is the file required?
            if ($this->required == 1)
            {
                SBC::userError('Please select a file to upload.');
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
                SBC::userError('Sorry, this filetype is not allowed for uploads.');
            }

            // Animated PNGs
            if ($this->allow_apng != 1 && $this->filetype == 'png')
            {
                // Check
                $apng_check = $this->identifyAPNG($this->buffer);
                if ($apng_check == 1)
                {
                    SBC::userError('Sorry, animated PNGs are not allowed for uploads.');
                }
            }

            // Check if the image can be read
            $imagesize = @getimagesize($file);
            if (!$imagesize)
            {
                SBC::userError('Image cannot be read. File may be corrupt.');
            }

            // Set image width and height
            $this->image_width  = $imagesize[0];
            $this->image_height = $imagesize[1];

            // Width Check
            if ($this->image_width < $this->width_min)
            {
                SBC::userError('Minimum image width must be at least '.$this->width_min.'px');
            }
            if ($this->image_width > $this->width_max)
            {
                SBC::userError('Maximum image width is '.$this->width_max.'px (width is '.$this->image_width.'px)');
            }

            // Height Check
            if ($this->image_height < $this->height_min)
            {
                SBC::userError('Minimum image height must be at least '.$this->height_min.'px');
            }
            if ($this->image_height > $this->height_max)
            {
                SBC::userError('Maximum image height is '.$this->height_max.'px (height is '.$this->image_height.'px)');
            }

            // Set has file
            $this->hasfile = 1;
        }
    }

    // Has Info
    final public function hasInfo()
    {
        $method = 'ImageFile->hasInfo()';

        if ($this->hasinfo != 1)
        {
            SBC::devError('Information is not set',$method);
        }
    }

    // Has File
    final public function hasFile()
    {
        $method = 'ImageFile->hasFile()';

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
        $method = 'ImageFile->setUserId()';

        // Has info?
        $this->hasInfo();

        // Check
        $user_id = isset($user_id) ? (int) $user_id : 0;
        if ($user_id < 1)
        {
            SBC::devError('$user_id is not set',$method);
        }

        // Set
        $this->user_id = 1;
        $this->isready = 1;
    }

    // Get Info
    final public function getInfo()
    {
        $method = 'ImageFile->getInfo()';

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

    // File Ready
    final private function isReady()
    {
        $method = 'ImageFile->isReady()';

        if ($this->isready != 1)
        {
            SBC::devError('$isready is not set',$method);
        }
    }

    // Create Image
    final public function createImage(&$db)
    {
        $method = 'ImageFile->createImage()';

        // Ready?
        $this->isReady();

        // Initialize
        $user_id        = $this->user_id;
        $time           = $this->time;
        $ip_address     = $this->ip_address;
        $filetype       = $this->filetype;
        $filesize       = $this->filesize;
        $rd             = $this->rd;
        $rd_code        = $this->rd_code;
        $image_width    = $this->image_width;
        $image_height   = $this->image_height;

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Insert image info into database
        $sql = 'INSERT INTO images
            SET rd=?,
            rd_code=?,
            user_id=?,
            date_created=?,
            date_updated=?,
            ip_created=?,
            ip_updated=?,
            filetype=?,
            filesize=?,
            image_width=?,
            image_height=?,
            isdeleted=1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('isiiisssiii',$rd,$rd_code,$user_id,$time,$time,$ip_address,$ip_address,$filetype,$filesize,$image_width,$image_height);
        SBC::statementExecute($stmt,$db,$sql,$method);

        // Get Image ID
        $sql = 'SELECT id
            FROM images
            WHERE rd=?
            AND user_id=?
            AND date_created=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('iii',$rd,$user_id,$time);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Image ID?
        $image_id   = isset($row['id']) ? (int) $row['id'] : 0;
        if ($image_id < 1)
        {
            SBC::devError('Could not insert image into database',$method);
        }
        $this->image_id = $image_id;

        // Create Filenames
        $folder     = 'img/';
        $filename   = $image_id.'-'.$rd_code.'.'.$filetype;
        $file_url   = $folder . $filename;

        // Create the File
        $createimage    = @copy($this->file,$file_url) or die('Could not create new file');

        // Set
        $this->thumb_name   = $image_id.'-'.$rd_code;
        $this->file_url     = $file_url;

        // Generate Thumbnail
        $this->generateThumbnail(325);

        // Mark image as not deleted
        $sql = 'UPDATE images
            SET isdeleted=0
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$image_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }

    // Get Image ID
    final public function getImageId()
    {
        return $this->image_id;
    }

    // Generate Thumbnail
    final private function generateThumbnail($thumb_size)
    {
        $method = 'ImageFile->generateThumbnail()';

        if ($thumb_size < 1 || $thumb_size > 1000)
        {
            SBC::devError('Invalid thumb size',$method);
        }

        // Initialize
        $image_source   = SBC::checkEmpty($this->file_url,'$this->file_url');
        $thumb_name     = SBC::checkEmpty($this->thumb_name,'$this->thumb_name');
        $file_datatype  = $this->filetype;
        $end_name       = $thumb_size;

        // Destination
        $file_destination   = 'img_thumb/'.$thumb_name;

        // GD Library
        if (extension_loaded('gd'))
        {
            // Set Thumbnail Name
            $thumbnail  = $file_destination.'_'.$end_name;

            // Get File Information
            $p_filesize = filesize($image_source);
            $p_sizes    = GetImageSize($image_source);
            $p_area     = $p_sizes[0] * $p_sizes[1];

            // Which side is bigger?
            if ($p_sizes[0] < $p_sizes[1])
            {
                // Tall Image
                $long_mode  = 1;
                $long_side  = $p_sizes[1];
                $short_side = $p_sizes[0];
            }
            else
            {
                // Wide Image
                $long_mode  = 0;
                $long_side  = $p_sizes[0];
                $short_side = $p_sizes[1];
            }

            // Set Functions
            $create_function    = 'imagecreatetruecolor';
            $resize_function    = 'imagecopyresampled';

            // Switch from File Type
            switch ($file_datatype)
            {
                case 'png':     $source = @imagecreatefrompng($image_source);
                                break;

                case 'jpg':     $source = @imagecreatefromjpeg($image_source);
                                break;

                case 'gif':     $source = @imagecreatefromgif($image_source);
                                break;

                default:        $source = '';
                                break;
            }
            if (empty($source))
            {
                SBC::devError('Image file not supported by GD',$method);
            }

            // Check if GD Barfed
            if (!$source)
            {
                SBC:devError('Sorry, GD Barfed :(',$method);
            }

            // Set Common Values
            $ratio      = $thumb_size / $long_side;
            $lesser_s   = round($short_side * $ratio);

            // PNGs
            if ($file_datatype == 'png')
            {
                // Start creating the thumbnail
                if ($long_mode == 0)
                {
                    $thumb = imagecreatetruecolor($thumb_size,$lesser_s);
                }
                else
                {
                    $thumb = imagecreatetruecolor($lesser_s,$thumb_size);
                }

                // PNG Options
                imagealphablending($thumb,false);
                imagesavealpha($thumb,true);
                $source = imagecreatefrompng($image_source);
                imagealphablending($source,true);

                // Which side?
                if ($long_mode == 0)
                {
                    imagecopyresampled($thumb,$source,0,0,0,0,$thumb_size,$lesser_s,$p_sizes[0],$p_sizes[1]);
                }
                else
                {
                    imagecopyresampled($thumb,$source,0,0,0,0,$lesser_s,$thumb_size,$p_sizes[0],$p_sizes[1]);
                }
                imagepng($thumb,$thumbnail.'.png');

                // Clear Memory
                imagedestroy($thumb);
                imagedestroy($source);
            }
            // JPGs
            else if ($file_datatype == 'jpg')
            {
                // Start
                if ($long_mode == 0)
                {
                    $new_s = $create_function($thumb_size,$lesser_s);
                    $resize_function($new_s,$source,0,0,0,0,$thumb_size,$lesser_s,$p_sizes[0],$p_sizes[1]);
                }
                else
                {
                    $new_s = $create_function($lesser_s,$thumb_size);
                    $resize_function($new_s,$source,0,0,0,0,$lesser_s,$thumb_size,$p_sizes[0],$p_sizes[1]);
                }
                imagejpeg($new_s,$thumbnail.'.jpg',100); // 100 quality

                // Clear Memory
                imagedestroy($new_s);
                imagedestroy($source);
            }
            else
            // GIFs
            // Let's create a PNG thumbnail instead since animated gifs can be problematic in filesize
            if ($file_datatype == 'gif')
            {
                // Which side?
                if ($long_mode == 0)
                {
                    $thumb = imagecreatetruecolor($thumb_size,$lesser_s);
                }
                else
                {
                    $thumb = imagecreatetruecolor($lesser_s,$thumb_size);
                }

                // PNG Options
                imagealphablending($thumb,false);
                imagesavealpha($thumb,true);
                $source = imagecreatefromgif($image_source);
                imagealphablending($source,true);

                // Which side?
                if ($long_mode == 0)
                {
                    imagecopyresampled($thumb,$source,0,0,0,0,$thumb_size,$lesser_s,$p_sizes[0],$p_sizes[1]);
                }
                else
                {
                    imagecopyresampled($thumb,$source,0,0,0,0,$lesser_s,$thumb_size,$p_sizes[0],$p_sizes[1]);
                }
                imagepng($thumb,$thumbnail.'.png');

                // Clear Memory
                imagedestroy($thumb);
                imagedestroy($source);
            }
            else
            {
                SBC::devError('Could not generate thumbnail',$method);
            }
        }
        else
        {
            SBC::devError('Could not load GD Library',$method);
        }
        
    }
}