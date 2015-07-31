<?php

namespace Greg\Http;

use Greg\Engine\InternalTrait;

class Request extends \Greg\Support\Http\Request
{
    use InternalTrait;

    static public function create($appName, array $param = [])
    {
        return static::newInstanceRef($appName, $param);
    }
}