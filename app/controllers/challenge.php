<?php
// @author          Kameloh
// @lastUpdated     2016-05-12

use SketchbookCafe\SBC\SBC as SBC;

class Challenge extends Controller
{
    protected $obj_array = '';

    public function __construct(&$obj_array)
    {
        $this->obj_array = &$obj_array;
    }

    // Main Page
    public function index()
    {
        // Model
        $PageObj = $this->model('ChallengesPage',$this->obj_array);
        $PageObj->process();

        // Set vars
        $result = $PageObj->getResult();
        $rownum = $PageObj->getRownum();
        $Member = &$this->obj_array['Member'];

        // View
        $this->view('sketchbookcafe/header');
        $this->view('challenges/index',
        [
            'result'    => &$result,
            'rownum'    => &$rownum,
            'Member'    => &$Member,
        ]);
        $this->view('sketchbookcafe/footer');
    }

    // Entry Submit
    public function entry_submit()
    {
        $method = 'challenge->entry_submit()';

        // Model
        $this->model('ChallengeEntrySubmit',$this->obj_array);
    }


}