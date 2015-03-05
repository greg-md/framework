<?php

namespace Greg\Db\Sql\Table;

use Greg\Db\Sql\Table;
use Greg\Storage\ArrayObject;
use Greg\Support\Obj;

abstract class RowAbstract extends ArrayObject
{
    protected $tableName = null;

    public function __construct($tableName, $data = [])
    {
        if (($tableName instanceof Table)) {
            $tableName = $tableName->name();
        }

        $this->tableName($tableName);

        return parent::__construct($data);
    }

    public function tableName($value = null)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, func_get_args());
    }
}