<?php

namespace Greg\Db\Sql\Query;

use Greg\Engine\InternalTrait;
use Greg\Support\Db\Sql\StorageInterface;
use Greg\Support\Tool\Str;

class Conditions extends \Greg\Support\Db\Sql\Query\Conditions
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
        return Conditions::create($this->appName(), $this->storage());
    }
}