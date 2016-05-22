<?php
// @author          Kameloh
// @lastUpdated     2016-05-22

class Streams extends Controller
{
    protected $obj_array = '';

    public function __construct(&$obj_array)
    {
        $this->obj_array = &$obj_array;
    }

    // Add Stream Submit
    public function add_stream_submit()
    {
        $this->model('StreamsAddSubmit',$this->obj_array);
    }

    // Add Stream
    public function add_stream()
    {
        $Page = $this->model('StreamsAddStreamPage',$this->obj_array);
        $Form = $Page->getForm();

        $this->view('sketchbookcafe/header');
        $this->view('streams/add_stream',
        [
            'Form'  => &$Form,
        ]);
        $this->view('sketchbookcafe/footer');

    }

    public function index()
    {
        $Page = $this->model('StreamsIndexPage',$this->obj_array);

        $this->view('sketchbookcafe/header');
        $this->view('streams/index');
        $this->view('sketchbookcafe/footer');
    }

}