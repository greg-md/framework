<?php

namespace Greg\Db\Sql\Storage\Mysql\Query;

class Select extends \Greg\Db\Sql\Query\Select
{
    use SelectTrait;

    const FOR_UPDATE = 'FOR UPDATE';

    const LOCK_IN_SHARE_MODE = 'LOCK IN SHARE MODE';

    public function toString()
    {
        $query = parent::toString();

        $this->addType($query);

        return $query;
    }
}