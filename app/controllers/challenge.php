<?php
// @author          Kameloh
// @lastUpdated     2016-05-04

use SketchbookCafe\SBC\SBC as SBC;

class Challenge extends Controller
{
    protected $obj_array = '';

    public function __construct(&$obj_array)
    {
        $this->obj_array = &$obj_array;
    }

    // Entry Submit
    public function entry_submit()
    {
        $method = 'challenge->entry_submit()';

        // Model
        $this->model('ChallengeEntrySubmit',$this->obj_array);
    }


}