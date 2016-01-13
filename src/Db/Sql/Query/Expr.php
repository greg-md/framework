<?php

namespace Greg\Db\Sql\Query;

use Greg\Tool\Obj;

class Expr
{
    protected $data = null;

    public function __construct($data)
    {
        $this->data($data);
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