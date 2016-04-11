<?php

class Logout extends Controller
{
    public function __construct()
    {
        
    }

    public function index()
    {
        $this->model('UserLogout');
    }
}