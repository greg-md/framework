<?php

namespace Greg\Db\Sql\Storage\Adapter\Pdo;

use Greg\Db\Sql\Storage\Adapter\StmtInterface;
use Greg\Engine\Internal;
use Greg\Engine\InternalInterface;

class Stmt extends \PDOStatement implements StmtInterface, InternalInterface
{
    use Internal;

    /**
     * \PDOStatement require it to be protected
     */
    protected function __construct() {}

    public function fetchOne($column = 0)
    {
        return parent::fetchColumn($column);
    }

    public function fetchPairs($key = 0, $value = 1)
    {
        $pairs = [];

        foreach(parent::fetchAll(\PDO::FETCH_NUM) as $row) {
            $pairs[$row[$key]] = $row[$value];
        }

        return $pairs;
    }
}