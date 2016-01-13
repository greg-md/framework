<?php

namespace Greg\Server;

use Greg\Tool\Arr;

class Server
{
    static public function scriptName()
    {
        return static::get('SCRIPT_NAME');
    }

    static public function scriptFile()
    {
        return static::get('SCRIPT_FILENAME');
    }

    static public function requestTime()
    {
        return static::get('REQUEST_TIME');
    }

    static public function requestMicroTime()
    {
        return static::get('REQUEST_TIME_FLOAT');
    }

    static public function documentRoot()
    {
        return static::get('DOCUMENT_ROOT');
    }

    static public function has($key, ...$keys)
    {
        return Arr::hasRef($_SERVER, $key, ...$keys);
    }

    static public function get($key, $else = null)
    {
        return Arr::get($_SERVER, $key, $else);
    }
}