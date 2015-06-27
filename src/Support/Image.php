<?php

namespace Greg\Support;

class Image extends File
{
    static public function type($file)
    {
        $type = function_exists('exif_imagetype') ? @exif_imagetype($file) : null;

        if (!$type) {
            list($width, $height, $type) = @getimagesize($file);

            unset($width, $height);

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

        unset($width);

        if (!$height) {
            $height = imagesy(static::get($file));
        }

        return $height;
    }

    static public function is($file)
    {
        return static::type($file) ? true : false;
    }

    static public function mime($file)
    {
        return static::typeToMime(static::type($file));
    }

    static public function typeToMime($type)
    {
        return image_type_to_mime_type($type);
    }

    static public function get($file)
    {
        $image = null;

        switch(static::type($file)) {
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

    static public function saveJPEG($image, $file, $fixDir = false, $quality = 75)
    {
        $fixDir && static::fixDir($file, true);

        imagejpeg($image, $file, $quality);

        return true;
    }

    static public function getJPEG($image, $quality = 75)
    {
        ob_start();

        imagejpeg($image, null, $quality);

        return ob_get_clean();
    }

    static public function saveGIF($image, $file, $fixDir = false)
    {
        $fixDir && static::fixDir($file, true);

        imagegif($image, $file);

        return true;
    }

    static public function getGIF($image)
    {
        ob_start();

        imagegif($image, null);

        return ob_get_clean();
    }

    static public function savePNG($image, $file, $fixDir = false, $quality = 9, $filters = PNG_NO_FILTER)
    {
        $fixDir && static::fixDir($file, true);

        imagepng($image, $file, $quality, $filters);

        return true;
    }

    static public function getPNG($image, $quality = 9, $filters = PNG_NO_FILTER)
    {
        ob_start();

        imagepng($image, null, $quality, $filters);

        return ob_get_clean();
    }
}