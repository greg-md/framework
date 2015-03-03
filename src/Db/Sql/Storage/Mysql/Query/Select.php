<?php

namespace Greg\Db\Sql\Storage\Mysql\Query;

class Select extends \Greg\Db\Sql\Query\Select
{
    protected function parseLimit(&$query)
    {
        if ($this->limit()) {
            $query[] = 'LIMIT ' . $this->limit();
        }

        if ($this->offset()) {
            $query[] = 'OFFSET ' . $this->offset();
        }

        return $this;
    }
}