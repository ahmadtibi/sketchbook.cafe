<?php
// @author          Kameloh
// @lastUpdated     2016-05-04
namespace SketchbookCafe\Images;

use SketchbookCafe\SBC\SBC as SBC;

class Images
{
    // SQL Stuff
    public $result = '';
    public $rownum = 0;

    // Vars
    private $id_list = '';
    public $image = [];

    // Construct
    public function __construct()
    {
        $method = 'Images->__construct()';
    }

    // Get Images
    final public function getImages(&$db)
    {
        $method = 'Images->getImages()';

        // Create List
        $id_list = SBC::idClean($this->id_list);

        if (!empty($id_list))
        {
            // Explode it
            $temp_array = explode(',',$id_list);
            foreach ($temp_array as $value)
            {
                // Create Array
                $this->image[$value]['id'] = $value;
            }

            // Switch
            $db->sql_switch('sketchbookcafe');

            // Get Images
            $sql = 'SELECT id, rd_code, user_id, filetype, filesize, image_width, image_height, isdeleted
                FROM images
                WHERE id IN('.$id_list.')';
            $this->result = $db->sql_query($sql);
            $this->rownum = $db->sql_numrows($this->result);

            // Set Arrays
            if ($this->rownum > 0)
            {
                // Set
                while ($trow = mysqli_fetch_assoc($this->result))
                {
                    $this->image[$trow['id']] = array
                    (
                        'id'            => $trow['id'],
                        'rd_code'       => $trow['rd_code'],
                        'user_id'       => $trow['user_id'],
                        'filetype'      => $trow['filetype'],
                        'image_width'   => $trow['image_width'],
                        'image_height'  => $trow['image_height'],
                        'isdeleted'     => $trow['isdeleted'],
                    );
                }
                mysqli_data_seek($this->result,0);
            }
        }

    }

    // Add String
    final public function addString($input)
    {
        $method = 'Images->addString()';

        if (!empty($input))
        {
            // Add
            $this->id_list .= $input.' ';
        }
    }

    // Add Rows
    final public function idAddRows($result,$column)
    {
        $method = 'Images->idAddRows()';

        // Result?
        if (!empty($result))
        {
            // Count
            $rownum = mysqli_num_rows($result);
            if ($rownum > 0)
            {
                // Loop
                while ($trow = mysqli_fetch_assoc($result))
                {
                    $id = $trow[$column];
                    if ($id > 0)
                    {
                        // Add
                        $this->id_list .= $id.' ';
                    }
                }
                mysqli_data_seek($result,0);
            }
        }
    }

    // Add One ID
    final public function idAddOne($id)
    {
        $method = 'Images->idAddOne()';

        if ($id > 0)
        {
            $this->id_list .= $id.' ';
        }
    }
}