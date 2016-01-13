<?php

namespace Greg\Application\Db\Sql\Query;

use Greg\Application\Engine\InternalTrait;
use Greg\Db\Sql\StorageInterface;
use Greg\Tool\Str;

class Insert extends \Greg\Db\Sql\Query\Insert
{
    use InternalTrait;

    static public function create($appName, StorageInterface $storage)
    {
        return static::newInstanceRef($appName, $storage);
    }

    protected function fetchAlias($name)
    {
        /* @var $name string|array|InternalTrait */

        if (Str::isScalar($name) and strpos($name, '\\') !== false) {
            $name = $name::instance($this->appName());
        }

        return parent::fetchAlias($name);
    }
}