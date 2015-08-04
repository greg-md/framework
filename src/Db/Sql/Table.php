<?php

namespace Greg\Db\Sql;

use Greg\Engine\InternalTrait;

class Table extends \Greg\Support\Db\Sql\Table
{
    use InternalTrait;

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