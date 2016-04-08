<?php
// Error Function
function error($message)
{
    // Global Database
    global $db;
    if ($db)
    {
        $db->close();
    }

    echo $message;
    exit;
}