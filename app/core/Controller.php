<?php

class Controller
{
    public function model($model,$data = [])
    {
        require_once '../app/models/'. $model . '.php';
        return new $model($data);
    }

    public function view($view, $data = [])
    {
        require_once '../app/views/'. $view .'.php';
    }
}