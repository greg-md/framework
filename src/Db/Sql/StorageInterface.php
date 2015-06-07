<?php

namespace Greg\Db\Sql;

interface StorageInterface
{
    /**
     * @param null $columns
     * @param null $_
     * @return Query\Select
     * @throws \Exception
     */
    public function select($columns = null, $_ = null);

    /**
     * @param null $into
     * @return Query\Insert
     * @throws \Exception
     */
    public function insert($into = null);

    /**
     * @param null $from
     * @param bool $delete
     * @return Query\Delete
     * @throws \Exception
     */
    public function delete($from = null, $delete = false);

    /**
     * @param null $table
     * @return Query\Update
     * @throws \Exception
     */
    public function update($table = null);

    public function getTableSchema($tableName);

    public function getTableInfo($tableName);

    public function getTableReferences($tableName);

    public function getTableRelationships($tableName);

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

    public function expr($expr);
}