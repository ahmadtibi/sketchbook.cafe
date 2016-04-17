<?php
// Admin

class Admin extends Controller
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
        $this->model('AdminPage',$this->obj_array);

        // View
        $this->view('sketchbookcafe/header');
        $this->view('admin/index');
        $this->view('sketchbookcafe/footer');
    }
}