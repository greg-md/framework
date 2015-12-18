<?php

namespace Greg\Application\Db\Sql;

use Greg\Application\Engine\InternalTrait;

class Table extends \Greg\Db\Sql\Table
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