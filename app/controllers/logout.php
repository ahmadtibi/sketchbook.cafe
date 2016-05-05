<?php
// @author          Kameloh
// @lastUpdated     2016-05-02
class Logout extends Controller
{
    protected $obj_array = '';

    public function __construct(&$obj_array)
    {
        $this->obj_array = &$obj_array;
    }

    public function index()
    {
        $this->model('UserLogout',$this->obj_array);
    }
}