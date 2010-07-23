<?php

class TagModel
{
    static function getTagsFromPhoto(Face_Photo $photo)
    {
        foreach ($photo->tags as $tag)
        {
            return new Face_Tag($tag);
        }
    }
}

?>