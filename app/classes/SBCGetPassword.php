<?php
// @author          Jonathan Maltezo (Kameloh)
// @lastUpdated     2016-04-26
namespace SketchbookCafe\SBCGetPassword;

use SketchbookCafe\SBC\SBC as SBC;

class SBCGetPassword
{
    // Construct
    public function __construct()
    {
        $method = 'SBCGetPassword->__construct()';
    }

    // Process Password
    final public static function process($password)
    {
        $method = 'SBCGetPassword->process()';

        // Clean
        $password = trim(addslashes($password));
        $password = str_replace('<?', 'x<x', $password);
        $password = str_replace('?>', 'x>x', $password);
        $password = str_replace('"', '&#34;', $password);
        $password = str_replace("'", '&#39;', $password);

        // Length Check
        if (isset($password{100}) || !isset($password{4})) {
            SBC::userError('Password must be between 5-100 characters.');
        }

        // If empty
        if (empty($password))
        {
            SBC::userError('Invalid password (empty)');
        }

        // Return
        return $password;
    }
}