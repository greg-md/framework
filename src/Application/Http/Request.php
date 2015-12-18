<?php

namespace Greg\Application\Http;

use Greg\Application\Engine\InternalTrait;
use Greg\Tool\Debug;

class Request extends \Greg\Http\Request
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

        $info['REQUEST'] = $_REQUEST;

        $info['FILES'] = $_FILES;

        return $info;
    }
}