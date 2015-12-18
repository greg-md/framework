<?php

namespace Greg\Db\Sql\Query;

use Greg\Db\Sql\Query;
use Greg\Tool\Debug;

class On extends Query
{
    use OnTrait;

    public function toString()
    {
        return $this->onToString(false);
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