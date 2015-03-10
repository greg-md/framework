<?php

namespace Greg\Db\Sql;

use Greg\Db\Sql\Query\Expr;
use Greg\Support\Obj;

trait StorageTrait
{
    public function expr($expr)
    {
        return Expr::create($this->appName(), $expr);
    }

    abstract public function appName($value = null, $type = Obj::PROP_REPLACE);
}