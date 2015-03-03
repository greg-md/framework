<?php

namespace Greg\Support;

class Image extends File
{
    static public function type($file)
    {
        $type = function_exists('exif_imagetype') ? @exif_imagetype($file) : null;

        if (!$type) {
            list($width, $height, $type) = @getimagesize($file);

            return $type;
        }

        return $type;
    }

    static public function typeToExt($type, $point = true)
    {
        return image_type_to_extension($type, $point);
    }

    static public function ext($file, $point = false)
    {
        $type = static::type($file);

        $ext = static::typeToExt($type, false);

        switch($ext) {
            case 'jpeg':
                $ext = 'jpg';

                break;
            case 'tiff':
                $ext = 'tif';

                break;
        }

        if ($point) {
            $ext = '.' . $ext;
        }

        return $ext;
    }

    static public function width($file)
    {
        list($width) = @getimagesize($file);

        if (!$width) {
            $width = imagesx(static::get($file));
        }

        return $width;
    }

    static public function height($file)
    {
        list($width, $height) = @getimagesize($file);

        if (!$height) {
            $height = imagesy(self::get($file));
        }

        return $height;
    }

    static public function is($file)
    {
        return self::type($file) ? true : false;
    }

    static public function mime($file)
    {
        return self::typeToMime(self::type($file));
    }

    static public function typeToMime($type)
    {
        return image_type_to_mime_type($type);
    }

    static public function get($file)
    {
        $image = null;

        switch(self::type($file)) {
            case IMAGETYPE_GIF:
                $image = imagecreatefromgif($file);

                break;
            case IMAGETYPE_JPEG:
                $image = imagecreatefromjpeg($file);

                break;
            case IMAGETYPE_PNG:
                $image = imagecreatefrompng($file);

                break;
        }

        if (!$image) {
            $image = imagecreatefromstring(file_get_contents($file));

            if (!$image) {
                throw new \Exception('Wrong file type.');
            }
        }

        return $image;
    }

    static public function saveJPEG($image, $file, $quality = 75, $fixDir = false)
    {
        $fixDir && static::fixDir($file, true);

        imagejpeg($image, $file, $quality);

        return true;
    }

    static public function saveGIF($image, $file, $fixDir = false)
    {
        $fixDir && static::fixDir($file, true);

        imagegif($image, $file);

        return true;
    }

    static public function savePNG($image, $file, $quality = 9, $fixDir = false, $filters = PNG_NO_FILTER)
    {
        $fixDir && static::fixDir($file, true);

        imagepng($image, $file, $quality, $filters);

        return true;
    }
}