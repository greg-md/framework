<?php

namespace Greg\Application\Db\Sql\Storage\Mysql\Query;

use Greg\Db\Sql\Storage\Mysql\Query\SelectTrait;

class Select extends \Greg\Application\Db\Sql\Query\Select
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