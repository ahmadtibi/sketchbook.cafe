<?php

class Controller
{
    public function model($model)
    {

/*
        // Globals
        global $db;

        $db->open();

        $sql = 'SELECT username
            FROM testmanhero
            WHERE id=1
            LIMIT 1';
        $result = $db->sql_query($sql);
        $row    = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        echo 'name is '.$row['username'];

        echo 'omg it opened and closed';

        $db->close();
*/

        require_once '../app/models/'. $model . '.php';
        return new $model();
    }

    public function view($view, $data = [])
    {
        require_once '../app/views/'. $view .'.php';
    }
}