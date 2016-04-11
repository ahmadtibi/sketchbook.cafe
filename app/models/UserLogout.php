<?php
// User logout stuff
class UserLogout
{
    public function __construct()
    {
        // Global
        global $User;

        // I guess we can logout with the user object?
        $User->logout();
    }
}