<?php
// Statement Error
function statement_error($action,$method)
{
    // Global Database (for now!)
    global $db;
    if ($db)
    {
        $db->close();
    }
    echo 'Could not execute statement('.$action.') for '.$method;
    exit;
}