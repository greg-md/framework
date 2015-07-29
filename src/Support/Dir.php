<?php

namespace Greg\Support;

class Dir
{
    static public function fix($dir, $recursive = false)
    {
        if (!file_exists($dir)) {
            ErrorHandler::throwException();

            @mkdir($dir, 0777, $recursive);

            ErrorHandler::restore();
        }

        return true;
    }

    static public function fixRecursive($dir)
    {
        return static::fix($dir, true);
    }
}