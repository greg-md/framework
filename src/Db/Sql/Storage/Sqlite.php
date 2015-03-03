<?php

namespace Greg\Db\Sql\Storage;

use Greg\Db\Sql\Storage;
use Greg\Db\Sql\Storage\Adapter\AdapterInterface;
use Greg\Db\Sql\Storage\Sqlite\Query\Insert;
use Greg\Db\Sql\Storage\Sqlite\Query\Select;
use Greg\Db\Sql\Storage\Sqlite\Query\Delete;
use Greg\Db\Sql\Storage\Sqlite\Query\Update;
use Greg\Support\Obj;

class Sqlite extends Storage
{
    protected $path = null;

    protected $adapterClass = '\Greg\Db\Sql\Storage\Sqlite\Adapter\Pdo';

    protected $adapter = null;

    public function __construct($path, $adapterClass = null)
    {
        $this->path($path);

        if ($adapterClass !== null) {
            $this->adapterClass($adapterClass);
        }

        return $this;
    }

    public function init()
    {
        /* @var $class \Greg\Db\Sql\Storage\Sqlite\Adapter\Pdo */
        $class = $this->adapterClass();

        $this->adapter($class::create($this->appName(), $this->path()));

        return $this;
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

        $query = Select::create($this->appName(), $this);

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
        $query = Insert::create($this->appName(), $this);

        if ($into !== null) {
            $query->into($into);
        }

        return $query;
    }

    /**
     * @param null $from
     * @return Delete
     * @throws \Exception
     */
    public function delete($from = null)
    {
        $query = Delete::create($this->appName(), $this);

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
        $query = Update::create($this->appName(), $this);

        if ($table !== null) {
            $query->table($table);
        }

        return $query;
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
        return call_user_func_array([$this->adapter(), __FUNCTION__], func_get_args());
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

    public function path($value = null, $type = Obj::VAR_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function adapterClass($value = null, $type = Obj::VAR_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    /**
     * @param AdapterInterface $value
     * @return AdapterInterface|null
     */
    public function adapter(AdapterInterface $value = null)
    {
        return Obj::fetchVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function __call($method, array $args = [])
    {
        return call_user_func_array([$this->adapter(), $method], $args);
    }
}