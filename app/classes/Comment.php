<?php
// Comment Class
class Comment
{
    // SQL Stuff
    public $result = '';
    public $rownum = 0;

    // Vars
    private $id_list = '';
    public $comment = [];

    // Construct
    public function __construct()
    {
    }

    final public function getComments(&$db)
    {
        // Global (for now);
        global $Member;

        // Functions
        sbc_function('id_clean');

        // Create list
        $id_list = id_clean($this->id_list);

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
                $this->comment[$value]['id'] = $value;
            }

            // Switch
            $db->sql_switch('sketchbookcafe');

            // Get Comments
            $sql = 'SELECT id, user_id, date_created, message, ismail, isprivate, isdeleted
                FROM sbc_comments
                WHERE id IN('.$id_list.')';
            $result = $db->sql_query($sql);
            $rownum = $db->sql_numrows($result);

            // Set Vars
            $this->result   = $result;
            $this->rownum   = $rownum;

            // Set Arrays
            while ($trow = mysqli_fetch_assoc($result))
            {
                $this->comment[$trow['id']] = array
                (
                    'id'            => $trow['id'],
                    'user_id'       => $trow['user_id'],
                    'date_created'  => $trow['date_created'],
                    'message'       => $trow['message'],
                    'ismail'        => $trow['ismail'],
                    'isprivate'     => $trow['isprivate'],
                    'isdeleted'     => $trow['isdeleted'],
                );
            }
            mysqli_data_seek($result,0);

            // Add User ID
            $Member->idAddRows($result,'user_id');
        }
    }

    // Get Date
    final public function getDate($id)
    {
        // Make sure ID is set
        $id = isset($id) ? (int) $id : 0;
        if ($id < 1)
        {
            return time();
        }

        // Comment ID
        $comment_id = isset($this->comment[$id]) ? $this->comment[$id] : 0;
        if ($comment_id < 1)
        {
            return time();
        }

        // Get Time
        return $this->comment[$id]['date_created'];
    }

    // Display Comment
    final public function displayComment($id)
    {
        // Make sure ID is set
        $id = isset($id) ? (int) $id : 0;
        if ($id < 1)
        {
            return null;
        }

        // Check if the comment exists
        $comment_id = isset($this->comment[$id]) ? $this->comment[$id] : 0;
        if ($comment_id < 1)
        {
            return 'Could not find comment';
        }

        // Comment deleted?
        $isdeleted  = $this->comment[$id]['isdeleted'];
        if ($isdeleted != 0)
        {
            return 'Comment no longer exists';
        }

        // Return
        return $this->comment[$id]['message'];
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

    // Add Single
    final public function idAddOne($id)
    {
        if ($id > 0)
        {
            $this->id_list .= $id.' ';
        }
    }
}