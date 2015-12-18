<?php

namespace Greg\Application\Db\Sql\Query;

use Greg\Application\Engine\InternalTrait;
use Greg\Db\Sql\StorageInterface;
use Greg\Tool\Str;

class Select extends \Greg\Db\Sql\Query\Select
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

    protected function newConditions()
    {
        return Where::create($this->appName(), $this->storage());
    }
}