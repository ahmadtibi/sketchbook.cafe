<?php
// Check Empty - checks if the value is empty
// If empty, return an error!
function check_empty($value,$name)
{
    // Set value
    $value = isset($value) ? $value : '';
    if (empty($value))
    {
        error($name.' is empty for function check_empty()');
    }

    // Return
    return $value;
}