<?php

namespace Greg\Db\Sql\Query\Traits;

trait Where
{
    protected $where = [];

    public function whereCol($column, $value = null, $operator = '=')
    {
        $this->where[] = [
            'logic' => 'and',
            'expr' => $this->quoteExpr($column) . ' ' . $operator . ' ?',
            'params' => [$value],
        ];

        return $this;
    }

    public function whereIn($column, array $values = null)
    {
        $this->where[] = [
            'logic' => 'and',
            'expr' => $this->quoteExpr($column) . ' IN (' . implode(', ', str_split(str_repeat('?', sizeof($values)))) . ')',
            'params' => $values,
        ];

        return $this;
    }

    public function where($expr = null, $value = null, $_ = null)
    {
        $params = func_get_args();

        if ($params) {
            array_shift($params);

            $this->where[] = [
                'logic' => 'and',
                'expr' => $expr,
                'params' => $params,
            ];

            return $this;
        }

        return $this->where;
    }

    public function whereToString()
    {
        $where = [];

        foreach($this->where as $info) {
            if (isset($info['expr'])) {
                $where[] = ($where ? ' ' . $info['logic'] . ' ' : '') . $this->quoteExpr($info['expr']);

                $this->bindParams($info['params']);
            }
        }

        return $where ? 'WHERE ' . implode('', $where) : '';
    }

    abstract protected function quoteExpr($expr);

    abstract protected function bindParams(array $params = []);
}