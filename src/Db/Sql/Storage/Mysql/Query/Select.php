<?php

namespace Greg\Db\Sql\Storage\Mysql\Query;

use Greg\Db\Sql\Storage\Mysql\QueryTrait;

class Select extends \Greg\Db\Sql\Query\Select
{
    use QueryTrait, SelectTrait;

    const FOR_UPDATE = 'FOR UPDATE';

    const LOCK_IN_SHARE_MODE = 'LOCK IN SHARE MODE';

    public function toString()
    {
        $query = parent::toString();

        $this->addType($query);

        return $query;
    }
}