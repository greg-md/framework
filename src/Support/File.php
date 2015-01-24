<?php

namespace Greg\Support;

class File
{
    static public function ext($file, $point = false)
    {
        $file = explode('.', $file);

        return ($point ? '.' : '') . (sizeof($file > 1) ? end($file) : null);
    }

    static public function mime($file)
    {
        return (new \finfo())->file($file, FILEINFO_MIME_TYPE);
    }

    static public function fixDir($file, $recursive = false)
    {
        return Dir::fix(dirname($file), $recursive);
    }
}