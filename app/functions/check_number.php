<?php
// Check Number: checks if the value is a valid number
// Cannot be less than 1!
function check_number($value,$name)
{
    error('CHECK_NUMBER is no longer used. Use SBC::checkNumber instead');

/*
    // Set value
    $value = isset($value) ? (int) $value : 0;
    if ($value < 1)
    {
        error('Dev error: '.$name.' is not set for function check_number()');
    }

    // return
    return $value;
*/
}