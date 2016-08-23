<?php

namespace Greg\Db\Sql\Storage\Mysql\Query;

trait SelectTrait
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

    protected $type = null;

    public function forUpdate()
    {
        $this->type(Select::FOR_UPDATE);

        return $this;
    }

    public function lockInShareMode()
    {
        $this->type(Select::LOCK_IN_SHARE_MODE);

        return $this;
    }

    public function addType($query)
    {
        switch($type = $this->type()) {
            case Select::FOR_UPDATE:
                $query .= ' FOR UPDATE';
                break;
            case Select::LOCK_IN_SHARE_MODE:
                $query .= ' LOCK IN SHARE MODE';
                break;
        }

        return $query;
    }

    public function type($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    abstract public function limit($value = null);

    abstract public function offset($value = null);
}