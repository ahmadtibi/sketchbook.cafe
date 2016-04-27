<?php
// @author          Jonathan Maltezo (Kameloh)
// @lastUpdated     2016-04-26
namespace SketchbookCafe\SBCGetEmail;

use SketchbookCafe\SBC\SBC as SBC;

class SBCGetEmail
{
    // Construct
    public function __construct()
    {
        $method = 'SBCGetEmail->__construct()';
    }

    // Process
    final public static function process($email)
    {
        $method = 'SBCGetEmail->process()';

        // Trim
        $email = isset($email) ? trim($email) : '';
        if (empty($email))
        {
            SBC::userError('E-mail is empty.');
        }

        // Min and max check 6-100.. !isset must be 5
        if (isset($email{100}) || !isset($email{5}))
        {
            SBC::userError('Invalid e-mail address.');
        }

        // PHP's Filter
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) === true)
        {
            SBC::userError('Invalid e-mail (PHP Filter)');
        }

        // Old check - just in case!
        if (preg_match('/[^A-Za-z0-9@._-]/',$email))
        {
            SBC::userError('Invalid e-mail address: invalid characters.');
        }

        // Empty?
        if (empty($email))
        {
            SBC::userError('Invalid e-mail (empty)');
        }

        // Return
        return $email;
    }
}