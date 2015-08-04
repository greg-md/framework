<?php

namespace Greg\Support\Db\Sql\Storage\Adapter;

use Greg\Support\Db\Sql\Storage;
use Greg\Support\Db\Sql\StorageInterface;

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
     * @param array $options
     * @return StmtInterface
     */
    public function prepare($query, array $options = []);

    public function query($statement);

    public function quote($string, $type = StorageInterface::PARAM_STR);

    public function rollBack();

    public function setAttribute($name, $value);
}