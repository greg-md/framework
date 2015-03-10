<?php

namespace Greg\Db\Sql\Table;

use Greg\Db\Sql\Table;
use Greg\Engine\Internal;
use Greg\Storage\ArrayObject;
use Greg\Support\Obj;

abstract class RowAbstract extends ArrayObject
{
    use Internal;

    protected $tableName = null;

    public function __construct($tableName, $data = [])
    {
        if (($tableName instanceof Table)) {
            $tableName = $tableName->name();
        }

        $this->tableName($tableName);

        parent::__construct($data);

        return $this;
    }

    public function tableName($value = null)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, func_get_args());
    }
}