<?php

namespace Greg\Db\Sql\Table;

use Greg\Db\Sql\Table;
use Greg\Engine\Internal;
use Greg\Storage\ArrayObject;
use Greg\Support\Debug;
use Greg\Support\Obj;

abstract class RowAbstract extends ArrayObject
{
    use Internal;

    protected $table = null;

    public function __construct(Table $table, $data = [])
    {
        $this->table($table);

        parent::__construct($data);

        return $this;
    }

    static public function create($appName, $tableName, $data = [])
    {
        return static::newInstanceRef($appName, $tableName, $data);
    }

    public function getTableName()
    {
        return $this->getTable()->getName();
    }

    /**
     * @return Table
     * @throws \Exception
     */
    public function getTable()
    {
        if (!($table = $this->table())) {
            throw new \Exception('Please define a table for this row.');
        }

        return $table;
    }

    /**
     * @param Table $value
     * @return $this|Table
     */
    public function table(Table $value = null)
    {
        return Obj::fetchVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function __debugInfo()
    {
        return Debug::fixInfo($this, get_object_vars($this), false);
    }
}