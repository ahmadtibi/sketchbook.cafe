<?php
// @author          Jonathan Maltezo (Kameloh)
// @lastUpdated     2016-04-27
// User logout stuff
class UserLogout
{
    public function __construct(&$obj_array)
    {
        $method = 'UserLogout->__construct()';

        // Initialize Objects
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // I guess we can logout with the user object?
        $User->logout($db);
    }
}