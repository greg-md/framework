<?php

namespace Greg\Http;

use Greg\Engine\InternalTrait;
use Greg\Support\Tool\Debug;

class Request extends \Greg\Support\Http\Request
{
    use InternalTrait;

    static public function create($appName, array $param = [])
    {
        return static::newInstanceRef($appName, $param);
    }

    public function __debugInfo()
    {
        $info = Debug::fixInfo($this, get_object_vars($this));

        $info['GET'] = $_GET;

        $info['POST'] = $_POST;

        $info['FILES'] = $_FILES;

        return $info;
    }
}