<?php
// @author          Kameloh
// @lastUpdated     2016-05-02

class Testmanhero extends Controller
{
    protected $obj_array = '';

    public function __construct(&$obj_array)
    {
        $this->obj_array = &$obj_array;
    }

    // Main page
    public function index()
    {
        // Model
        $this->model('TestmanheroIndex',$this->obj_array);

        // View
        $this->view('sketchbookcafe/header');
        $this->view('testmanhero/index');
        $this->view('sketchbookcafe/footer');
    }
}