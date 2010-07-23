<?php

class Face_User
{
    protected $_user;
    
    function __construct($objectOrArray)
    {
        if (is_array($classOrArray)) {
            $this->_user = (object) $classOrArray;
        } else if (is_object($classOrArray)) {
            $this->_user = $classOrArray;
        } else{
            throw new Exception ('Face_User requires an object or array representing user data to be passed');
        }
    }
    
    function __get($name)
    {
        if ($this->_user->{$name}){
            return $this->_user->{$name};
        }
        
        return $this->{$name}
    }
    
}

?>