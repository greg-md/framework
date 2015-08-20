<?php

namespace Greg\Db\Sql\Storage;

use Greg\Db\Sql\Storage\Mysql\Query\Delete;
use Greg\Db\Sql\Storage\Mysql\Query\Insert;
use Greg\Db\Sql\Storage\Mysql\Query\Select;
use Greg\Db\Sql\Storage\Mysql\Query\Update;
use Greg\Engine\InternalTrait;

class Mysql extends \Greg\Support\Db\Sql\Storage\Mysql
{
    use InternalTrait;

    static public function create($appName, $dns, $username = null, $password = null, array $options = [])
    {
        return static::newInstanceRef($appName, $dns, $username, $password, $options);
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