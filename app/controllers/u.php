<?php
// @author          Kameloh
// @lastUpdated     2016-05-16

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\SBCGetUsername\SBCGetUsername as SBCGetUsername;

class U extends Controller
{
    protected $obj_array = '';

    public function __construct(&$obj_array)
    {
        $this->obj_array = &$obj_array;
    }

    public function index($username = '')
    {
        $method = 'U->index()';

        $username = SBCGetUsername::process($username);

        $Page = $this->model('UPage',$this->obj_array);
        $Page->setUsername($username);
        $Page->process();
        $user_row       = $Page->getUserRow();
        $entries_result = $Page->getEntriesResult();
        $entries_rownum = $Page->getEntriesRownum();

        // View
        $this->view('sketchbookcafe/header');
        $this->view('userprofile/index',
        [
            'User'              => &$this->obj_array['User'],
            'user_row'          => &$user_row,
            'entries_result'    => $entries_result,
            'entries_rownum'    => $entries_rownum,
        ]);
        $this->view('sketchbookcafe/footer');
    }
}