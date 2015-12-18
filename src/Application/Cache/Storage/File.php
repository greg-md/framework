<?php

namespace Greg\Application\Cache\Storage;

use Greg\Application\Engine\InternalTrait;

class File extends \Greg\Cache\Storage\File
{
    use InternalTrait;

    static public function create($appName, $path, $schema = null)
    {
        return static::newInstanceRef($appName, $path, $schema);
    }
}