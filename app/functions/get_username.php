<?php
// Get username
function get_username($username)
{
    // Quick Clean
    $username = isset($username) ? trim(addslashes($username)) : '';

    // Length Check (3-20 characters long... !isset must be 2)
    if (isset($username{20}) || !isset($username{2}))
    {
        error('Username must be between 3-20 characters.');
    }

    // Letters only
    if (preg_match('/[^A-Za-z]/',$username))
    {
        error('Usernames may only contain letters a-z');
    }

    // Just in case
    if (empty($username))
    {
        error('Invalid username (blank)');
    }

    // Return
    return $username;
}