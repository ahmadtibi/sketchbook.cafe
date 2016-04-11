<?php
// Clean IDs function - cleans a string and converts it to a mysql friendly variable
function id_clean($input)
{
    // Replace commas
    $input  = str_replace(',',' ',$input);

    // Clean Spaces
    $input  = preg_replace('/\s+/',' ',$input);

    // Trim extra spaces and convert to array
    $input  = explode(' ',trim($input));

    // Unique Values Only
    $input  = array_unique($input);

    // Convert to friendly mysql
    $input  = implode(',',$input);

    // Return
    return $input;
}