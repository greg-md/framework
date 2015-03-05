<?php

namespace Greg\Db\Sql\Query;

use Greg\Engine\Internal;
use Greg\Engine\InternalInterface;
use Greg\Support\Obj;

class Expr implements InternalInterface
{
    use Internal;

    protected $data = null;

    public function __construct($data)
    {
        $this->data($data);
    }

    public function data($value = null, $type = Obj::VAR_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function __toString()
    {
        return $this->data();
    }
}