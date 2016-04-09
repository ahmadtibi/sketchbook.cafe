<?php
/**
*
* Class Require
* @desc         Similar to require_once but we have an array we can use for development purposes.
* 
* @author       Jonathan Maltezo (Kameloh)
* @copyright    (c) 2016, Jonathan Maltezo (Kameloh)
* @lastUpdated  2016-04-09
*
*/
function sbc_class($name)
{
    // Global
    global $sbc_class;

    // Check if array is set
    $sbc_class[$name] = isset($sbc_class[$name]) ? $sbc_class[$name] : 0;
    if ($sbc_class[$name] != 1)
    {
        require '../app/classes/' . $name . '.php';

        // Set as included
        $sbc_class[$name] = 1;
    }
}