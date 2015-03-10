<?php

namespace Greg\Db\Sql;

use Greg\Engine\Internal;
use Greg\Support\Obj;

class Table
{
    use Internal;

    protected $prefix = null;

    protected $name = null;

    protected $alias = null;

    protected $columns = [];

    protected $autoIncrement = null;

    protected $primary = [];

    protected $unique = [];

    protected $references = [];

    protected $relationships = [];

    protected $dependencies = [];

    protected $rowClass = 'Greg\Db\Sql\Table\Row';

    protected $rowFullClass = 'Greg\Db\Sql\Table\RowFull';

    protected $rowSetClass = 'Greg\Db\Sql\Table\RowSet';

    //protected $rowTreeClass = 'Greg\Db\Sql\Table\RowTree';

    //protected $rowSetTreeClass = 'Greg\Db\Sql\Table\RowSetTree';

    //protected $rowSetPaginationClass = 'Greg\Db\Sql\Table\RowSetPagination';

    protected $scaffolding = [];

    protected $nameColumn = null;

    protected $label = null;

    //protected $treeColumns = null;

    //protected $treeParentColumns = null;

    protected $storage = null;

    public function __construct(StorageInterface $storage)
    {
        $this->storage($storage);

        return $this;
    }

    public function init()
    {
        if (method_exists($this, 'loadSchema')) {
            $this->loadSchema();
        }

        return $this;
    }

    public function addColumns(array $columns)
    {
        /* @var $column Table\Column */
        foreach($columns as $column) {
            $this->columns($column->name(), $column);
        }

        return $this;
    }

    public function select($columns = null, $_ = null)
    {
        if (!is_array($columns)) {
            $columns = func_get_args();
        }

        return $this->storage()->select($columns)->table($this)->from($this);
    }

    public function createRow($data, $reset = true)
    {
        $class = $this->rowClass();

        if (!$class) {
            throw Exception::create($this->appName(), 'Undefined table row class.');
        }

        if ($reset) {
            $rowData = [];

            /* @var $column Table\Column */
            foreach($this->columns() as $name => $column) {
                $rowData[$column->name()] = $column->def();
            }

            $data = array_merge($rowData, $data);
        }

        return $this->app()->binder()->newInstance($class, $this, $data);
    }

    public function createRowSet($data)
    {
        $class = $this->rowSetClass();

        if (!$class) {
            throw Exception::create($this->appName(), 'Undefined table row set class.');
        }

        return $this->app()->binder()->newInstance($class, $this, $data);
    }

    public function prefix($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function name($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function alias($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function columns($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function autoIncrement($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function primary($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function unique($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function references($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function relationships($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function dependencies($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function rowClass($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function rowFullClass($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function rowSetClass($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function scaffolding($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false, $recursive = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function nameColumn($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function label($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    /**
     * @param StorageInterface $value
     * @return StorageInterface|null
     */
    public function storage(StorageInterface $value = null)
    {
        return Obj::fetchVar($this, $this->{__FUNCTION__}, func_get_args());
    }
}