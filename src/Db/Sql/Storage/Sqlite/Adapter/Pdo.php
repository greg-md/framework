<?php

namespace Greg\Db\Sql\Storage\Sqlite\Adapter;

class Pdo extends \Greg\Db\Sql\Storage\Adapter\Pdo
{
    protected $stmtClass = '\Greg\Db\Sql\Storage\Sqlite\Adapter\Pdo\Stmt';

    public function __construct($path)
    {
        parent::__construct('sqlite:' . $path);

        return $this;
    }
}