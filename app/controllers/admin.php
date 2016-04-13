<?php
// Admin

class Admin extends Controller
{
    public function __construct()
    {

    }

    // Main Page
    public function index()
    {
        // Model
        $this->model('AdminPage');
        $this->view('admin/index');
    }
}