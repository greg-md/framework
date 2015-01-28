<?php

namespace Greg\Db\Storage\Adapter\Pdo;

use Greg\Db\Storage\Adapter\StmtInterface;
use Greg\Engine\Internal;

class Stmt extends \PDOStatement implements StmtInterface
{
    use Internal;

    protected function __construct() {}

    public function fetchOne($column = 0)
    {
        return parent::fetchColumn($column);
    }
}