<?php
// @author          Kameloh
// @lastUpdated     2016-05-04
// Master image handler: creates and manages images in the database
namespace SketchbookCafe\Image;

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\GenerateRandom\GenerateRandom as GenerateRandom;

class Image
{
    // Set
    private $time = 0;
    private $rd = 0;
    private $user_id = 0;
    private $ip_address = '';

    // Generated
    private $rd_code = '';
    private $image_id = 0;

    private $db;

    // Construct
    public function __construct(&$db)
    {
        $this->db           = &$db;
        $this->ip_address   = SBC::getIpAddress();
        $this->time         = SBC::getTime();
        $this->rd           = SBC::rd();
        $this->rd_code      = GenerateRandom::process(5);
    }

    // New Image
    final public function newImage($input)
    {
        $method = 'Image->newImage()';

        // Initialize
        $user_id    = isset($input['user_id']) ? (int) $input['user_id'] : 0;
        if ($user_id < 1)
        {
            SBC::devError('$user_id is not set',$method);
        }
        $this->user_id = $user_id;

        // Switch
        $db->sql_switch('sketchbookcafe');



        // Create a new 
    }
}