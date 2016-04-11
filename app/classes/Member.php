<?php
// Member class - contains all members visible on the page
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

    }

    // Get Users
    final public function getUsers(&$db)
    {
        // Functions
        sbc_function('id_clean');

        // Create list
        $id_list = id_clean($this->id_list);

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
            $sql = 'SELECT id, username
                FROM users
                WHERE id IN('.$id_list.')';
            $result = $db->sql_query($sql);
            $rownum = $db->sql_numrows($result);

            // Set vars
            $this->result   = $result;
            $this->rownum   = $rownum;
        }
    }

    final public function addString($input)
    {
        if (!empty($input))
        {
            // Add
            $this->id_list .= $input.' ';
        }
    }

    // Add Rows: adds user ids from an sql result
    final public function idAddRows($result,$column)
    {
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
        if ($id > 0)
        {
            $this->id_list .= $id.' ';
        }
    }





}