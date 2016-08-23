<?php

namespace Greg\Db\Sql\Storage\Adapter\Pdo;

use Greg\Db\Sql\Storage\Adapter\Pdo;
use Greg\Db\Sql\Storage\Adapter\StmtInterface;
use Greg\Support\Arr;
use Greg\Support\Type;

class Stmt extends \PDOStatement implements StmtInterface
{
    /**
     * \PDOStatement require it to be protected
     */
    protected function __construct() {}

    protected $pdo = null;

    public function fetchOne($column = 0)
    {
        if (Type::isNaturalNumber($column)) {
            return parent::fetchColumn($column);
        }

        $row = $this->fetchAssoc();

        return $row ? Arr::get($row, $column) : null;
    }

    public function fetchColumn($column = 0)
    {
        return array_column($this->fetchAll(), $column);
    }

    public function fetchPairs($key = 0, $value = 1)
    {
        $pairs = [];

        foreach(parent::fetchAll() as $row) {
            $pairs[$row[$key]] = $row[$value];
        }

        return $pairs;
    }

    public function fetchAssoc()
    {
        return parent::fetch(\PDO::FETCH_ASSOC);
    }

    public function fetchAssocAll()
    {
        return parent::fetchAll(\PDO::FETCH_ASSOC);
    }

    public function execute($params = null)
    {
        return $this->callParent(__FUNCTION__, func_get_args());
    }

    protected function callParent($method, array $args = [])
    {
        try {
            return $this->_callParent($method, $args);
        } catch (\PDOException $e) {
            if ($e->errorInfo[1] == 2006) {
                $this->pdo()->reconnect();

                return $this->_callParent($method, $args);
            }
            throw $e;
        }
    }

    protected function _callParent($method, array $args = [])
    {
        $result = call_user_func_array(['parent', $method], $args);

        if ($result === false) {
            $this->errorCheck();
        }

        return $result;
    }

    public function errorCheck()
    {
        $errorInfo = $this->errorInfo();

        // Bind or column index out of range
        if ($errorInfo[1] and $errorInfo[1] != 25) {
            throw new \Exception($errorInfo[2]);
        }

        return $this;
    }

    public function nextRows()
    {
        return parent::nextRowset();
    }

    /**
     * @param Pdo|null $value
     * @return $this|null|Pdo
     */
    public function pdo(Pdo $value = null)
    {
        return Obj::fetchVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }
}