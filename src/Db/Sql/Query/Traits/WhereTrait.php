<?php

namespace Greg\Db\Sql\Query\Traits;

use Greg\Db\Sql\Query\Where;
use Greg\Db\Sql\StorageInterface;
use Greg\Support\Arr;
use Greg\Support\Obj;

trait WhereTrait
{
    protected $where = [];

    public function where($expr = null, $value = null, $_ = null)
    {
        if ($args = func_get_args()) {
            return $this->whereLogic('AND', ...$args);
        }

        return $this->where;
    }

    public function hasWhere()
    {
        return (bool)$this->where;
    }

    public function clearWhere()
    {
        $this->where = [];

        return $this;
    }

    public function orWhere($expr, $value = null, $_ = null)
    {
        return $this->whereLogic('OR', ...func_get_args());
    }

    public function whereCol($column, $operator, $value = null)
    {
        return $this->whereColLogic('AND', ...func_get_args());
    }

    public function orWhereCol($column, $operator, $value = null)
    {
        return $this->whereColLogic('OR', ...func_get_args());
    }

    public function whereCols(array $columns)
    {
        foreach($columns as $column => $value) {
            $this->whereCol($column, $value);
        }

        return $this;
    }

    public function orWhereCols(array $columns)
    {
        foreach($columns as $column => $value) {
            $this->orWhereCol($column, $value);
        }

        return $this;
    }

    /**
     * Support formats:
     * col1 => 1
     * col1 => [1, 2]
     * [col1] => [1]
     * [col1] => [[1], [2]]
     * [col1, col2] => [1, 2]
     * [col1, col2] => [[1, 2], [3, 4]]
     *
     * @param $type
     * @param $column
     * @param $operator
     * @param null $value
     * @return array|WhereTrait
     */
    public function whereColLogic($type, $column, $operator, $value = null)
    {
        $args = func_get_args();

        array_shift($args);

        if (sizeof($args) < 3) {
            $column = array_shift($args);

            $value = array_shift($args);

            $operator = null;
        }

        $isRow = false;

        if (is_array($column)) {
            $value = Arr::bring($value);

            if (sizeof($column) > 1) {
                $isRow = true;

                $column = array_map([$this, 'quoteExpr'], $column);

                $column = '(' . implode(', ', $column) . ')';
            } else {
                $column = current($column);

                $value = is_array($val = current($value)) ? array_merge(...$value) : $val;
            }
        }

        if ($isRow) {
            $valueExpr = $this->bindArrayExpr(array_map([$this, 'bindExpr'], $value));

            if (!$operator and is_array(current($value))) {
                $operator = 'IN';
            }

            $value = array_merge(...$value);
        } else {
            $column = $this->quoteExpr($column);

            $valueExpr = $this->bindExpr($value);

            if (!$operator and is_array($value)) {
                $operator = 'IN';
            }
        }

        $expr = $column . ' ' . ($operator ?: '=') . ' ' . $valueExpr;

        return $this->whereLogic($type, $expr, is_array($value) ? $value : [$value]);
    }

    public function whereLogic($logic, $expr, $param = null, $_ = null)
    {
        if (is_callable($expr)) {
            $query = Where::create($this->appName(), $this->storage());

            $expr($query);

            $expr = $query->toString();

            $param = $query->bindParams();
        }

        if (!is_array($param)) {
            $param = func_get_args();

            array_shift($param);

            array_shift($param);
        }

        $this->where[] = [
            'logic' => $logic,
            'expr' => $expr,
            'params' => $param,
        ];

        return $this;
    }

    protected function bindExpr($value)
    {
        return is_array($value) ? $this->bindArrayExpr($value) : '?';
    }

    protected function bindArrayExpr($value)
    {
        return '(' . implode(', ', array_fill(0, sizeof(Arr::bring($value)), '?')) . ')';
    }

    public function whereToString($useTag = true)
    {
        $where = [];

        foreach($this->where as $info) {
            if ($info['expr']) {
                $where[] = ($where ? ' ' . $info['logic'] . ' ' : '') . $this->quoteExpr($info['expr']);

                $this->bindParams($info['params']);
            }
        }

        return $where ? ($useTag ? 'WHERE ' : '') . implode('', $where) : '';
    }

    abstract public function appName($value = null, $type = Obj::PROP_REPLACE);

    /**
     * @param StorageInterface $value
     * @return $this|null|StorageInterface
     */
    abstract public function storage(StorageInterface $value = null);

    abstract protected function quoteExpr($expr);

    abstract protected function bindParams(array $params = []);
}