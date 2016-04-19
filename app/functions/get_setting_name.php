<?php
// Get Setting Name - used for Ajax Message Previewer
function get_setting_name($value)
{
    // Quick Clean
    $value = isset($value) ? trim(addslashes($value)) : '';

    // Lowercase
    $value = strtolower($value);

    // Length Check
    if (isset($value{60}))
    {
        error('Invalid Setting Name');
    }

    // Letters and Underscores Only
    if (preg_match('/[^a-z_]/',$value))
    {
        error('Setting Name may only contain letters a-z and underscores');
    }

    // Return
    return $value;
}