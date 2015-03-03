<?php

namespace Greg\Db\Sql;

interface StorageInterface
{
    /**
     * @param null $columns
     * @param null $_
     * @return Query\Select
     */
    public function select($columns = null, $_ = null);

    public function insert($into = null);

    public function delete($from = null);

    public function update($table = null);

    public function beginTransaction();

    public function commit();

    public function errorCode();

    public function errorInfo();

    public function exec($query);

    public function getAttribute($name);

    public function inTransaction();

    public function lastInsertId($name = null);

    /**
     * @param $query
     * @return Storage\Adapter\StmtInterface
     */
    public function prepare($query);

    public function query($query, $mode = null, $_ = null);

    public function quote($string, $type = Storage::PARAM_STR);

    public function rollBack();

    public function setAttribute($name, $value);
}