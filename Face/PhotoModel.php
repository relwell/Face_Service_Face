<?php

require_once 'Photo.php';

class Face_PhotoModel
{
    
    static function getPhotosFromResponse(stdClass $jsonObject)
    {
        $photos = array();
        
        foreach ($jsonObject->photos as $photo)
        
            $photos[] = new Face_Photo($photo);
        }
        
        return $photos;
    }
    
}

?>