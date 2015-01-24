<?php

namespace Greg\Server;

class Info
{
    static public function &scriptName()
    {
        return static::get('SCRIPT_NAME');
    }

    static public function &requestTime()
    {
        return static::get('REQUEST_TIME');
    }

    static public function &requestMicroTime()
    {
        return static::get('REQUEST_TIME_FLOAT');
    }

    static public function has($index)
    {
        return array_key_exists($index, $_SERVER);
    }

    static public function &get($index, $else = null)
    {
        if (static::has($index)) return $_SERVER[$index]; return $else;
    }
}