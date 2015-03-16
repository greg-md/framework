<?php

namespace Greg\Db\Sql\Storage\Adapter;

use Greg\Engine\Internal;
use Greg\Support\Obj;

class Pdo extends \PDO implements AdapterInterface
{
    protected $stmtClass = Pdo\Stmt::class;

    use Internal;

    public function init()
    {
        if ($this->stmtClass()) {
            $this->setAttribute(PDO::ATTR_STATEMENT_CLASS, [$this->stmtClass(), [$this]]);
        }

        $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        return $this;
    }

    public function stmtClass($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function errorCheck()
    {
        $errorInfo = $this->errorInfo();

        // Note: Ignoring error - bind or column index out of range
        if ($errorInfo[1] and $errorInfo[1] != 25) {
            throw Exception::newInstance($this->appName(), $errorInfo[2]);
        }

        return $this;
    }

    public function prepare($query, $options = [])
    {
        $stmt = parent::prepare($query, $options);

        if ($stmt === false) {
            $this->errorCheck();
        }

        return $stmt;
    }
}