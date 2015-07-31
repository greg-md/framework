<?php

namespace Greg\Db\Sql\Query;

use Greg\Engine\InternalTrait;
use Greg\Support\Obj;

class Expr
{
    use InternalTrait;

    protected $data = null;

    public function __construct($data)
    {
        $this->data($data);
    }

    static public function create($appName, $data)
    {
        return static::newInstanceRef($appName, $data);
    }

    public function data($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function __toString()
    {
        return $this->data();
    }
}