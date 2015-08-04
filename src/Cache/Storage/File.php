<?php

namespace Greg\Cache\Storage;

use Greg\Engine\InternalTrait;

class File extends \Greg\Support\Cache\Storage\File
{
    use InternalTrait;

    static public function create($appName, $path, $schema = null)
    {
        return static::newInstanceRef($appName, $path, $schema);
    }
}