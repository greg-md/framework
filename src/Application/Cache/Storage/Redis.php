<?php

namespace Greg\Application\Cache\Storage;

use Greg\Application\Engine\InternalTrait;

class Redis extends \Greg\Cache\Storage\Redis
{
    use InternalTrait;

    static public function create($appName, $host = null, $port = null, $prefix = null, $timeout = null)
    {
        return static::newInstanceRef($appName, $host, $port, $prefix, $timeout);
    }
}