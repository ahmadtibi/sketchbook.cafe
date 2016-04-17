<?php
// User logout stuff
class UserLogout
{
    public function __construct(&$obj_array)
    {
        // Initialize Objects
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // I guess we can logout with the user object?
        $User->logout($db);
    }
}