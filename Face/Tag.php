<?php

class Tag
{
    function __construct($classOrArray)
    {
        if (is_array($classOrArray)) {
            $this->_tag = (object) $classOrArray;
        } else if (is_object($classOrArray)) {
            $this->_tag = $classOrArray;
        } else{
            throw new Exception ('Face_Photo requires an object or array representing photo data to be passed');
        }
    }
    
    function __get($name)
    {
        if ($this->_tag->{$name}){
            return $this->_tag->{$name};
        }
        
        return $this->{$name}
    }
}

?>