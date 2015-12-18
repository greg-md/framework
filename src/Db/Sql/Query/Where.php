<?php

namespace Greg\Db\Sql\Query;

use Greg\Db\Sql\Query;
use Greg\Tool\Debug;

class Where extends Query
{
    use WhereTrait;

    public function toString()
    {
        return $this->whereToString(false);
    }

    public function __toString()
    {
        return $this->toString();
    }

    public function __debugInfo()
    {
        return Debug::fixInfo($this, get_object_vars($this), false);
    }
}