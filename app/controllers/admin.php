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

        // View
        require 'header.php';
        $this->view('admin/index');
        require 'footer.php';
    }
}