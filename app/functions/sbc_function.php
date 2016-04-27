<?php
/**
*
* Function Require
* @desc         Similar to require_once but we have an array we can use for development purposes.
* 
* @author       Jonathan Maltezo (Kameloh)
* @copyright    (c) 2016, Jonathan Maltezo (Kameloh)
* @lastUpdated  2016-04-09
*
*/
// Sketchbook Cafe Function Require
function sbc_function($name)
{
    error('SBC_FUNCTION is noo longer used');

    // Global
    global $sbc_function;

    // Check if array is set
    $sbc_function[$name] = isset($sbc_function[$name]) ? $sbc_function[$name] : 0;
    if ($sbc_function[$name] != 1)
    {
        require '../app/functions/' . $name . '.php';

        // Set as included
        $sbc_function[$name] = 1;
    }
}