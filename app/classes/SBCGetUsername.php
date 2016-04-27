<?php
// @author          Jonathan Maltezo (Kameloh)
// @lastUpdated     2016-04-26
namespace SketchbookCafe\SBCGetUsername;

use SketchbookCafe\SBC\SBC as SBC;

class SBCGetUsername
{
    // Construct
    public function __construct($username)
    {
        $method = 'SBCGetUsername->__construct()';
    }

    // Process Username
    final public static function process($username)
    {
        $method = 'SBCGetUsername->process()';

        // Quick Clean
        $username = isset($username) ? trim(addslashes($username)) : '';

        // Length Check (3-20 characters long... !isset must be 2)
        if (isset($username{20}) || !isset($username{2}))
        {
            SBC::userError('Username must be between 3-20 characters.');
        }

        // Letters only
        if (preg_match('/[^A-Za-z]/',$username))
        {
            SBC::userError('Usernames may only contain letters a-z');
        }

        // Just in case
        if (empty($username))
        {
            SBC::userError('Invalid username (blank)');
        }

        // Return
        return $username;
    }
}