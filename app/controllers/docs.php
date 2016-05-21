<?php
// @author          Kameloh
// @lastUpdated     2016-05-21

class Docs extends Controller
{
    protected $obj_array;

    public function __construct(&$obj_array)
    {
        $this->obj_array = &$obj_array;
    }

    public function tos()
    {
        // Model
        $Page = $this->model('TosPage',$this->obj_array);

        // View
        $this->view('sketchbookcafe/header');
        $this->view('docs/tos');
        $this->view('sketchbookcafe/footer');
    }

    public function index()
    {
        // Model
        $Page = $this->model('DocsIndexPage',$this->obj_array);

        // View
        $this->view('sketchbookcafe/header');
        $this->view('docs/index');
        $this->view('sketchbookcafe/footer');
    }
}