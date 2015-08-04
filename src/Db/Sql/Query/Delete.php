<?php

namespace Greg\Db\Sql\Query;

use Greg\Engine\InternalTrait;
use Greg\Support\Db\Sql\StorageInterface;
use Greg\Support\Tool\Str;

class Delete extends \Greg\Support\Db\Sql\Query\Delete
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

    protected function newWhere()
    {
        return Where::create($this->appName(), $this->storage());
    }
}