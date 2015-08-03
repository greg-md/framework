<?php

namespace Greg\Support\Db\Sql\Storage\Sqlite\Query;

class Select extends \Greg\Support\Db\Sql\Query\Select
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