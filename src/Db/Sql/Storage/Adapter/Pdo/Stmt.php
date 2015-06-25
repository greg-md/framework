<?php

namespace Greg\Db\Sql\Storage\Adapter\Pdo;

use Greg\Db\Sql\Storage\Adapter\StmtInterface;
use Greg\Support\Engine\Internal;
use Greg\Support\Arr;
use Greg\Support\Type;

class Stmt extends \PDOStatement implements StmtInterface
{
    use Internal;

    /**
     * \PDOStatement require it to be protected
     */
    protected function __construct() {}

    public function fetchOne($column = 0)
    {
        if (Type::isNaturalNumber($column)) {
            return parent::fetchColumn($column);
        }

        $row = $this->fetchAssoc();

        return $row ? Arr::get($row, $column) : null;
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

    public function errorCheck()
    {
        $errorInfo = $this->errorInfo();

        // Note: Ignoring error - bind or column index out of range
        if ($errorInfo[1] and $errorInfo[1] != 25) {
            throw new \Exception($errorInfo[2]);
        }

        return $this;
    }

    public function execute($params = null)
    {
        $result = parent::execute(...($params !== null ? [$params] : []));

        if ($result === false) {
            $this->errorCheck();
        }

        return $result;
    }

    public function nextRows()
    {
        return parent::nextRowset();
    }
}