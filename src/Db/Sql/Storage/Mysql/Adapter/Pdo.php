<?php

namespace Greg\Db\Sql\Storage\Mysql\Adapter;

class Pdo extends \Greg\Db\Sql\Storage\Adapter\Pdo
{
    protected $stmtClass = Pdo\Stmt::class;

    public function __construct($dsn, $username = null, $password = null, $options = [])
    {
        if (is_array($dsn)) {
            foreach($dsn as $key => &$value) {
                $value = $key . '=' . $value;
            }
            unset($value);

            $dsn = implode(';', $dsn);
        }

        parent::__construct('mysql:' . $dsn, $username, $password, $options);

        return $this;
    }
}