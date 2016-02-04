<?php

namespace Greg\Db\Sql\Storage\Mysql\Query;

use Greg\Tool\Obj;

class Select extends \Greg\Db\Sql\Query\Select
{
    use SelectTrait;

    const FOR_UPDATE = 'FOR UPDATE';

    const LOCK_IN_SHARE_MODE = 'LOCK IN SHARE MODE';

    protected $type = null;

    public function forUpdate()
    {
        $this->type(static::FOR_UPDATE);

        return $this;
    }

    public function lockInShareMode()
    {
        $this->type(static::LOCK_IN_SHARE_MODE);

        return $this;
    }

    public function toString()
    {
        $query = parent::toString();

        if ($type = $this->type()) {
            $query .= ' ' . $type;
        }

        return $query;
    }

    public function type($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }
}