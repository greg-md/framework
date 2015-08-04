<?php

namespace Greg\Support\Db\Sql;

interface StorageInterface
{
    const PARAM_BOOL = 5;

    const PARAM_NULL = 0;

    const PARAM_INT = 1;

    const PARAM_STR = 2;

    const PARAM_LOB = 3;

    const PARAM_STMT = 4;

    const FETCH_ORI_NEXT = 0;

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

    public function quote($string, $type = StorageInterface::PARAM_STR);

    public function rollBack();

    public function setAttribute($name, $value);
}