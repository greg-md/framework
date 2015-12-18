<?php

namespace Greg\Application\Cache\Storage;

use Greg\Application\Engine\InternalTrait;

class Sqlite extends \Greg\Cache\Storage\Sqlite
{
    use InternalTrait;

    static public function create($appName, $path)
    {
        return static::newInstanceRef($appName, $path);
    }
}