<?php

namespace Greg\Db;

use Greg\Db\Query\Expr;
use Greg\Support\Obj;

trait StorageTrait
{
    public function expr($expr)
    {
        return Expr::create($this->appName(), $expr);
    }

    abstract public function appName($value = null, $type = Obj::VAR_REPLACE);
}