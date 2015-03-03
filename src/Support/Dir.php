<?php

namespace Greg\Support;

class Dir
{
    static public function fix($dir, $recursive = false)
    {
        if (!file_exists($dir)) {
            set_error_handler(['static', 'throwErrors']);

            @mkdir($dir, 0777, $recursive);

            restore_error_handler();
        }

        return true;
    }

    static public function throwErrors($errNo, $errStr, $errFile, $errLine)
    {
        throw new \Exception($errStr);
    }
}