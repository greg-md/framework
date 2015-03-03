<?php

namespace Greg\Db\Sql\Storage\Mysql\Adapter;

class Pdo extends \Greg\Db\Sql\Storage\Adapter\Pdo
{
    protected $stmtClass = '\Greg\Db\Sql\Storage\Mysql\Adapter\Pdo\Stmt';

    public function __construct($dns, $username = null, $password = null, $options = [])
    {
        if (is_array($dns)) {
            foreach($dns as $key => &$value) {
                $value = $key . '=' . $value;
            }
            unset($value);

            $dns = implode(';', $dns);
        }

        parent::__construct('mysql:' . $dns, $username, $password, $options);

        return $this;
    }
}