<?php

namespace Greg\Db\Sql\Storage;

use Greg\Db\Sql\Query\Delete;
use Greg\Db\Sql\Query\Insert;
use Greg\Db\Sql\Query\Select;
use Greg\Db\Sql\Query\Update;
use Greg\Engine\InternalTrait;

class Sqlite extends \Greg\Support\Db\Sql\Storage\Sqlite
{
    use InternalTrait;

    static public function create($appName, $path, $adapter = null)
    {
        return static::newInstanceRef($appName, $path, $adapter);
    }

    protected function newSelect()
    {
        return Select::newInstance($this->appName(), $this);
    }

    protected function newInsert()
    {
        return Insert::newInstance($this->appName(), $this);
    }

    protected function newDelete()
    {
        return Delete::newInstance($this->appName(), $this);
    }

    protected function newUpdate()
    {
        return Update::newInstance($this->appName(), $this);
    }
}