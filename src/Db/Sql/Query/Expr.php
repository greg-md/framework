<?php

namespace Greg\Db\Sql\Query;

class Expr
{
    protected $data = null;

    protected $params = [];

    public function __construct($data, ...$params)
    {
        $this->data($data);

        $this->params($params);
    }

    public function data($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function params($key = null, $value = null, $type = Obj::PROP_APPEND)
    {
        return Obj::fetchArrayReplaceVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function toString()
    {
        return $this->data();
    }

    public function __toString()
    {
        return $this->toString();
    }
}