<?php
// @author          Kameloh
// @lastUpdated     2016-05-17
namespace SketchbookCafe\Member;

use SketchbookCafe\SBC\SBC as SBC;

class Member
{
    // SQL Stuff
    public $result = '';
    public $rownum = 0;

    // Vars
    private $id_list = '';
    public $member = [];

    // Construct
    public function __construct()
    {
        $method = 'Member->__construct()';
    }

    // Get Users
    final public function getUsers(&$db)
    {
        $method = 'Member->getUsers()';

        // Create list
        $id_list = SBC::idClean($this->id_list);

        // Make sure ID list is not empty
        if (!empty($id_list))
        {

            // Explode it
            $temp_array = explode(',',$id_list);
            foreach ($temp_array as $value)
            {
                // Create Array
                $this->member[$value]['id'] = $value;
            }

            // Switch
            $db->sql_switch('sketchbookcafe');

            // Get members
            $sql = 'SELECT id, username, avatar_id, avatar_url, title, forumsignature, 
                sketch_points, total_posts
                FROM users
                WHERE id IN('.$id_list.')';
            $result = $db->sql_query($sql);
            $rownum = $db->sql_numrows($result);

            // Set vars
            $this->result   = $result;
            $this->rownum   = $rownum;

            // Set Arrays
            while ($trow = mysqli_fetch_assoc($result))
            {
                $this->member[$trow['id']] = array
                (
                    'username'          => $trow['username'],
                    'title'             => $trow['title'],
                    'forumsignature'    => $trow['forumsignature'], 
                    'total_posts'       => $trow['total_posts'],
                    'sketch_points'     => $trow['sketch_points'],
                );
            }
            mysqli_data_seek($result,0);
        }
    }

    // Display Username
    final public function displayUsername($id)
    {
        $method = 'Member->displayUsername()';

        return $this->member[$id]['username'];
    }

    // Display Posts
    final public function displayPosts($id)
    {
        $method = 'Member->displayPosts()';

        return $this->member[$id]['total_posts'];
    }

    // Display Sketch Points
    final public function displaySketchPoints($id)
    {
        $method = 'Member->displaySketchPoints()';

        return $this->member[$id]['sketch_points'];
    }

    // Display Title
    final public function displayTitle($id)
    {
        $method = 'Member->displayTitle()';

        return $this->member[$id]['title'];
    }

    // Data Not Empty
    final public function notEmpty($id,$data)
    {
        $method = 'Member->notEmpty()';

        if (!empty($this->member[$id][$data]))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    // Display Forum Signature
    final public function displayForumSignature($id)
    {
        $method = 'Member->displayForumSignature()';

        return $this->member[$id]['forumsignature'];
    }

    // Add String
    final public function addString($input)
    {
        $method = 'Member->addString()';

        if (!empty($input))
        {
            // Add
            $this->id_list .= $input.' ';
        }
    }

    // Add Rows: adds user ids from an sql result
    final public function idAddRows($result,$column)
    {
        $method = 'Member->idAddRows()';

        // Do we have a result?
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
        $method = 'Member->idAddOne()';

        if ($id > 0)
        {
            $this->id_list .= $id.' ';
        }
    }
}