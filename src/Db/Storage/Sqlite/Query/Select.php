<?php

namespace Greg\Db\Storage\Sqlite\Query;

use Greg\Db\Query\Select as QuerySelect;
use Greg\Support\Obj;

class Select extends QuerySelect
{
    protected $limit = null;

    protected $offset = null;

    /**
     * @param null $value
     * @return self|int
     */
    public function limit($value = null)
    {
        return Obj::fetchIntVar($this, $this->{__FUNCTION__}, func_get_args(), true);
    }

    /**
     * @param null $value
     * @return self|int
     */
    public function offset($value = null)
    {
        return Obj::fetchIntVar($this, $this->{__FUNCTION__}, func_get_args(), true);
    }

    public function toString()
    {
        $query = [parent::toString()];

        if ($this->limit()) {
            $query[] = 'LIMIT ' . $this->limit();
        }

        if ($this->offset()) {
            $query[] = 'OFFSET ' . $this->offset();
        }

        return implode(' ', $query);
    }
}