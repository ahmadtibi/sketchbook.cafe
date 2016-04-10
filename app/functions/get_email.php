<?php
// Get E-mail
function get_email($email)
{
    // Trim
    $email = isset($email) ? trim($email) : '';
    if (empty($email))
    {
        error('E-mail is empty.');
    }

    // Min and max check 6-100.. !isset must be 5
    if (isset($email{100}) || !isset($email{5}))
    {
        error('Invalid e-mail address.');
    }

    // PHP's Filter
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) === true)
    {
        error('Invalid e-mail (PHP Filter)');
    }

    // Old check - just in case!
    if (preg_match('/[^A-Za-z0-9@._-]/',$email))
    {
        error('Invalid e-mail address: invalid characters.');
    }

    // Empty?
    if (empty($email))
    {
        error('Invalid e-mail (empty)');
    }

    // Return
    return $email;
}