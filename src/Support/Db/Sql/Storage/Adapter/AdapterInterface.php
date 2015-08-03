<?php

namespace Greg\Support\Db\Sql\Storage\Adapter;

use Greg\Support\Db\Sql\Storage;

interface AdapterInterface
{
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
     * @return StmtInterface
     */
    public function prepare($query);

    public function query();

    public function quote($string, $type = Storage::PARAM_STR);

    public function rollBack();

    public function setAttribute($name, $value);
}