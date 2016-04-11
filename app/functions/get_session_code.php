<?php
// Get session code - mainly used for cookies
function get_session_code($input)
{
    // Quick clean
    $value  = isset($input) ? trim(addslashes($input)) : '';

    // Check max size and character check
    if (isset($value{254}) || preg_match('/[^A-Za-z0-9]/',$value))
    {
        error('Invalid session. Please <a href="https://www.sketchbook.cafe/logout/>logout</a> and try again.');
    }

    // Return
    return $value;
}