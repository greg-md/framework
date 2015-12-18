<?php

namespace Greg\Db\Sql\Query;

use Greg\Db\Sql\Query;
use Greg\Tool\Debug;

class Conditions extends Query
{
    use ConditionsTrait;

    public function toString()
    {
        return $this->conditionsToString();
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