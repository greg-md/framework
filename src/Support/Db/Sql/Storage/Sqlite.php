<?php

namespace Greg\Support\Db\Sql\Storage;

use Greg\Support\Db\Sql\Storage;
use Greg\Support\Db\Sql\Storage\Adapter\AdapterInterface;
use Greg\Support\Db\Sql\Storage\Sqlite\Query\Insert;
use Greg\Support\Db\Sql\Storage\Sqlite\Query\Select;
use Greg\Support\Db\Sql\Storage\Sqlite\Query\Delete;
use Greg\Support\Db\Sql\Storage\Sqlite\Query\Update;
use Greg\Support\Obj;

class Sqlite extends Storage
{
    protected $path = null;

    protected $adapter = Sqlite\Adapter\Pdo::class;

    public function __construct($path, $adapter = null)
    {
        $this->path($path);

        if ($adapter !== null) {
            $this->adapter($adapter);
        }

        return $this;
    }

    static public function create($appName, $path, $adapter = null)
    {
        return static::newInstanceRef($appName, $path, $adapter);
    }

    /**
     * @param null $columns
     * @param null $_
     * @return Select
     * @throws \Exception
     */
    public function select($columns = null, $_ = null)
    {
        if (!is_array($columns)) {
            $columns = func_get_args();
        }

        $query = Select::newInstance($this->appName(), $this);

        if ($columns) {
            $query->columns($columns);
        }

        return $query;
    }

    /**
     * @param null $into
     * @return Insert
     * @throws \Exception
     */
    public function insert($into = null)
    {
        $query = Insert::newInstance($this->appName(), $this);

        if ($into !== null) {
            $query->into($into);
        }

        return $query;
    }

    /**
     * @param null $from
     * @param bool $delete
     * @return Delete
     * @throws \Exception
     */
    public function delete($from = null, $delete = false)
    {
        $query = Delete::newInstance($this->appName(), $this);

        if ($from !== null) {
            $query->from($from);
        }

        return $query;
    }

    /**
     * @param null $table
     * @return Update
     * @throws \Exception
     */
    public function update($table = null)
    {
        $query = Update::newInstance($this->appName(), $this);

        if ($table !== null) {
            $query->table($table);
        }

        return $query;
    }

    public function getTableSchema($tableName)
    {

    }

    public function getTableInfo($tableName)
    {

    }

    public function getTableReferences($tableName)
    {

    }

    public function getTableRelationships($tableName)
    {

    }

    public function beginTransaction()
    {
        return $this->adapter()->beginTransaction();
    }

    public function commit()
    {
        return $this->adapter()->commit();
    }

    public function errorCode()
    {
        return $this->adapter()->errorCode();
    }

    public function errorInfo()
    {
        return $this->adapter()->errorInfo();
    }

    public function exec($query)
    {
        return $this->adapter()->exec($query);
    }

    public function getAttribute($name)
    {
        return $this->adapter()->getAttribute($name);
    }

    public function inTransaction()
    {
        return $this->adapter()->inTransaction();
    }

    public function lastInsertId($name = null)
    {
        return $this->adapter()->lastInsertId($name);
    }

    /**
     * @param $query
     * @param array $options
     * @return Adapter\StmtInterface
     */
    public function prepare($query, $options = [])
    {
        return $this->adapter()->prepare($query, $options = []);
    }

    public function query($query, $mode = null, $_ = null)
    {
        return $this->adapter()->query(...func_get_args());
    }

    public function quote($string, $type = self::PARAM_STR)
    {
        return $this->adapter()->quote($string, $type);
    }

    public function rollBack()
    {
        return $this->adapter()->rollBack();
    }

    public function setAttribute($name, $value)
    {
        return $this->adapter()->setAttribute($name, $value);
    }

    public function path($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    /**
     * @param AdapterInterface $value
     * @return AdapterInterface|null
     */
    public function adapter(AdapterInterface $value = null)
    {
        return Obj::fetchCallableVar($this, $this->{__FUNCTION__}, function($adapter) {
            if (!is_object($adapter)) {
                /* @var $adapter \Greg\Engine\InternalTrait */
                $adapter = $adapter::newInstance($this->appName(), $this->path());
            }

            return $adapter;
        }, ...func_get_args());
    }

    public function __call($method, array $args = [])
    {
        return $this->adapter()->$method(...$args);
    }
}