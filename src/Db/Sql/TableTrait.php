<?php

namespace Greg\Db\Sql;

use Greg\Db\Sql\Table\Column;
use Greg\Tool\Arr;

trait TableTrait
{
    protected $prefix = null;

    protected $name = null;

    protected $alias = null;

    protected $columns = [];

    protected $customColumnsTypes = [];

    protected $autoIncrement = null;

    protected $primaryKeys = [];

    protected $uniqueKeys = [];

    protected $references = [];

    protected $relationships = [];

    protected $dependencies = [];

    protected $relationshipsAliases = [];

    protected $referencesAliases = [];

    protected $nameColumn = null;

    /**
     * @var StorageInterface|null
     */
    protected $storage = null;

    public function getInfo()
    {
        return $this->getStorage()->getTableInfo($this->getFullName());
    }

    public function getFirstUniqueKeys()
    {
        if ($autoIncrement = $this->getAutoIncrement()) {
            return [$autoIncrement];
        }

        if ($primaryKeys = $this->getPrimaryKeys()) {
            return $primaryKeys;
        }

        if ($uniqueKeys = current($this->getAllUniqueKeys())) {
            return $uniqueKeys;
        }

        return array_keys($this->getColumns());
    }

    public function combineFirstUniqueKeys($values)
    {
        Arr::bringRef($values);

        if (!$keys = $this->getFirstUniqueKeys()) {
            throw new \Exception('Table does not have primary keys.');
        }

        if (sizeof($keys) !== sizeof($values)) {
            throw new \Exception('Unique columns count should be the same as keys count.');
        }

        return array_combine($keys, $values);
    }

    public function getPrefix()
    {
        return $this->prefix;
    }

    public function setPrefix($name)
    {
        $this->prefix = (string)$name;

        return $this;
    }

    public function getFullName()
    {
        return $this->getPrefix() . $this->getName();
    }

    public function getName()
    {
        if (!$this->name) {
            throw new \Exception('Table name is not defined.');
        }

        return $this->name;
    }

    public function hasName()
    {
        return $this->name ? true : false;
    }

    public function setName($name)
    {
        $this->name = (string)$name;

        return $this;
    }

    public function getAlias()
    {
        return $this->alias;
    }

    public function setAlias($name)
    {
        $this->alias = (string)$name;

        return $this;
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function setColumns(array $columns)
    {
        $this->columns = [];

        foreach($columns as $column) {
            $this->addColumn($column);
        }

        return $this;
    }

    public function addColumn(Column $column)
    {
        $this->columns[] = $column;

        return $this;
    }

    /*
    public function customColumnsTypes($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }
    */

    public function getAutoIncrement()
    {
        return $this->autoIncrement;
    }

    public function setAutoIncrement($columnName)
    {
        $this->autoIncrement = (string)$columnName;

        return $this;
    }

    public function getPrimaryKeys()
    {
        return $this->primaryKeys;
    }

    public function setPrimaryKeys($columnsNames)
    {
        $this->primaryKeys = Arr::bring($columnsNames);

        return $this;
    }

    public function getAllUniqueKeys()
    {
        return $this->uniqueKeys;
    }

    public function setAllUniqueKeys(array $allColumnsNames)
    {
        $this->uniqueKeys = [];

        foreach($allColumnsNames as $columnsNames) {
            $this->uniqueKeys[] = Arr::bring($columnsNames);
        }

        return $this;
    }

    /*
    public function references($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function relationships($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }
    */

    public function getDependencies()
    {
        return $this->dependencies;
    }

    public function addDependence($name, $tableName, array $filter = [])
    {
        $this->dependencies[$name] = [
            'tableName' => $tableName,
            'filter' => $filter,
        ];

        return $this;
    }

    /*
    public function relationshipsAliases($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function referencesAliases($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }
    */

    public function getNameColumn()
    {
        return $this->nameColumn;
    }

    public function setNameColumn($name)
    {
        $this->nameColumn = (string)$name;

        return $this;
    }

    public function getStorage()
    {
        if (!$this->storage) {
            throw new \Exception('Table storage is not defined.');
        }

        return $this->storage;
    }

    public function setStorage(StorageInterface $storage)
    {
        $this->storage = $storage;

        return $this;
    }
}