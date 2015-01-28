<?php

namespace Greg\Db\Storage\Sqlite\Adapter;

class Pdo extends \Greg\Db\Storage\Adapter\Pdo
{
    protected $stmtClass = '\\Greg\\Db\\Storage\\Sqlite\\Adapter\\Pdo\\Stmt';

    public function __construct($path)
    {
        parent::__construct('sqlite:' . $path);

        return $this;
    }
}