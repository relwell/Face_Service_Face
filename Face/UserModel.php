<?php

require_once 'User.php';

class Face_UserModel
{
    static function getUsersFromResponse(array $userSet)
    {
        $users = array();
        
        foreach ($userSet as $user)
        {
            $users[] = new Face_User($user);
        }
        
        return $users;
    }
}

?>