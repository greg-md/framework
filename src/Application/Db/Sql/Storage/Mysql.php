<?php

namespace Greg\Application\Db\Sql\Storage;

use Greg\Application\Db\Sql\Storage\Mysql\Query\Delete;
use Greg\Application\Db\Sql\Storage\Mysql\Query\Insert;
use Greg\Application\Db\Sql\Storage\Mysql\Query\Select;
use Greg\Application\Db\Sql\Storage\Mysql\Query\Update;
use Greg\Application\Engine\InternalTrait;

class Mysql extends \Greg\Db\Sql\Storage\Mysql
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