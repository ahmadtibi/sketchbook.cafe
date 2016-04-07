<?php

class Controller
{
    public function model($model)
    {
        // Globals
        global $db;

        $db->open();


        echo 'omg it opened and closed';

        $db->close();

        require_once '../app/models/'. $model . '.php';
        return new $model();
    }

    public function view($view, $data = [])
    {
        require_once '../app/views/'. $view .'.php';
    }
}