<?php

namespace Greg\Db\Sql;

use Greg\Engine\InternalTrait;

class Table extends \Greg\Support\Db\Sql\Table
{
    use InternalTrait;

    protected function loadClassInstance($className, ...$args)
    {
        return $this->app()->loadInstance($className, ...$args);
    }

    protected function callCallable(callable $callable, ...$args)
    {
        return $this->app()->binder()->call($callable, ...$args);
    }

    public function getRelationshipTable($name)
    {
        /* @var $table Table */
        $table = parent::getRelationshipTable($name);

        if (!is_object($table)) {
            $table = $table::instance($this->appName());
        }

        return $table;
    }
}