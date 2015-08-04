<?php

namespace Greg\Db\Sql\Storage;

use Greg\Db\Sql\Storage\Sqlite\Query\Delete;
use Greg\Db\Sql\Storage\Sqlite\Query\Insert;
use Greg\Db\Sql\Storage\Sqlite\Query\Select;
use Greg\Db\Sql\Storage\Sqlite\Query\Update;
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
        return Select::create($this->appName(), $this);
    }

    protected function newInsert()
    {
        return Insert::create($this->appName(), $this);
    }

    protected function newDelete()
    {
        return Delete::create($this->appName(), $this);
    }

    protected function newUpdate()
    {
        return Update::create($this->appName(), $this);
    }
}