<?php
// @author          Kameloh
// @lastUpdated     2016-05-16
namespace SketchbookCafe\SBCChallenges;

use SketchbookCafe\SBC\SBC as SBC;

class SBCChallenges
{
    private $challenge_row = [];
    private $result = '';
    private $rownum = 0;

    private $id_list = '';

    private $obj_array = [];

    public function __construct(&$obj_array)
    {
        $this->obj_array = &$obj_array;
    }

    // Process Result
    final public function process()
    {
        $method = 'SBCChallenges->process()';

        // Initialize
        $Member     = &$this->obj_array['Member'];
        $db         = &$this->obj_array['db'];
        $id_list    = SBC::idClean($this->id_list);
        if (empty($id_list))
        {
            return null;
        }

        // Explode it
        $temp_array = explode(',',$id_list);
        foreach ($temp_array as $value)
        {
            $this->challenge_row[$value]['id'] = $value;
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Challenges
        $sql = 'SELECT id, category_id, thread_id, owner_user_id, date_created, date_updated,
            points, name, description, requirements, images_array, total_entries, ispending, isdeleted
            FROM challenges
            WHERE id IN('.$id_list.')';
        $this->result   = $db->sql_query($sql);
        $this->rownum   = $db->sql_numrows($this->result);

        if ($this->rownum > 0)
        {
            // Add
            $Member->idAddRows($this->result,'owner_user_id');
            while ($trow = mysqli_fetch_assoc($this->result))
            {
                $this->challenge_row[$trow['id']] = $trow;
            }
            mysqli_data_seek($this->result,0);
        }
    }

    // Add Rows
    final public function idAddRows($result,$column)
    {
        $method = 'SBCChallenges->idAddRows()';

        if (!empty($result))
        {
            if (mysqli_num_rows($result) > 0)
            {
                while ($trow = mysqli_fetch_assoc($result))
                {
                    if ($trow[$column] > 0)
                    {
                        $this->id_list .= $trow[$column].' ';
                    }
                }
                mysqli_data_seek($result,0);
            }
        }
    }

    // Add Single
    final public function idAddOne($id)
    {
        $method = 'SBCChallenges->idAddOne()';

        if ($id > 0)
        {
            $this->id_list .= $id.' ';
        }
    }

    // Get Challenge Row
    final public function getChallengeRow()
    {
        return $this->challenge_row;
    }
}