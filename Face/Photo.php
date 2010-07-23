<?php

/**
 * class to encapsulate photo data
 */

class Face_Photo
{
    protected $_photo;
    
    function __construct($classOrArray)
    {
        if (is_array($classOrArray)) {
            $this->_photo = (object) $classOrArray;
        } else if (is_object($classOrArray)) {
            $this->_photo = $classOrArray;
        } else{
            throw new Exception ('Face_Photo requires an object or array representing photo data to be passed');
        }
        
        
    }
    
    function __get($name)
    {
        if ($this->_photo->{$name}){
            return $this->_photo->{$name};
        }
        
        return $this->{$name}
    }
    
}