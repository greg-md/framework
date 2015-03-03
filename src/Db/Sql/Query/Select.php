<?php

namespace Greg\Db\Sql\Query;

use Greg\Db\Sql\Query;
use Greg\Db\Sql\QueryTrait;
use Greg\Db\Sql\Query\Traits\From;
use Greg\Db\Sql\Query\Traits\Where;
use Greg\Engine\Internal;
use Greg\Engine\InternalInterface;
use Greg\Support\Obj;

/**
 * Class Select
 * @package Greg\Db\Sql\Query
 *
 * @method Select whereCol($column, $value = null, $operator = '=')
 */
class Select implements InternalInterface
{
    use From, Where, QueryTrait, Internal;

    const ORDER_ASC = 'ASC';

    const ORDER_DESC = 'DESC';

    protected $distinct = false;

    protected $columns = [];

    protected $order = [];

    protected $limit = null;

    protected $offset = null;

    public function columns($columns = null, $_ = null)
    {
        if (func_num_args()) {

            if (!is_array($columns)) {
                $columns = func_get_args();
            }

            $this->columns = array_merge($this->columns, $columns);

            return $this;
        }

        return $this->columns;
    }

    public function order($expr = null, $type = null)
    {
        if (func_num_args()) {
            if ($type and !in_array($type, [static::ORDER_ASC, static::ORDER_DESC])) {
                throw Exception::create($this->appName(), 'Wrong select order type.');
            }

            $this->order[] = [
                'expr' => $expr,
                'type' => $type,
            ];

            return $this;
        }

        return $this->columns;
    }

    /**
     * @param null $table
     * @param null $columns
     * @param null $_
     * @return Select|array
     */
    public function from($table = null, $columns = null, $_ = null)
    {
        if (func_num_args()) {
            $this->from[] = $table;

            if (!is_array($columns)) {
                $columns = func_get_args();

                array_shift($columns);
            }

            if ($columns) {
                list($tableAlias) = $this->fetchAlias($table);

                if ($tableAlias) {
                    foreach($columns as &$column) {
                        if ($this->isCleanColumn($column)) {
                            $column = $tableAlias . '.' . $column;
                        }
                    }
                    unset($column);
                }

                $this->columns($columns);
            }

            return $this;
        }

        return $this->from;
    }

    protected function parseLimit(&$query)
    {
        return $this;
    }

    public function orderToString()
    {
        $order = [];

        foreach($this->order as $info) {
            $order[] = $this->quoteExpr($info['expr']) . ($info['type'] ? ' ' . $info['type'] : '');
        }

        return $order ? 'ORDER BY ' . implode(', ', $order) : '';
    }

    public function toString()
    {
        $this->clearBindParams();

        $query = [];

        $query[] = 'SELECT';

        if ($this->distinct()) {
            $query[] = 'DISTINCT';
        }

        $columns = $this->columns();
        if ($columns) {
            $cols = [];
            foreach($columns as $column) {
                $cols[] = $this->quoteAliasExpr($column);
            }
            $query[] = implode(', ', $cols);
        } else {
            $query[] = '*';
        }

        if ($from = $this->fromToString()) {
            $query[] = $from;
        }

        if ($where = $this->whereToString()) {
            $query[] = $where;
        }

        if ($order = $this->orderToString()) {
            $query[] = $order;
        }

        $this->parseLimit($query);

        return implode(' ', $query);
    }

    public function distinct($value = null)
    {
        return Obj::fetchBoolVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function one($column = 0)
    {
        $stmt = $this->storage()->prepare($this->toString());

        $this->bindParamsToStmt($stmt);

        $stmt->execute();

        return $stmt->fetchOne($column);
    }

    public function pairs($key = 0, $value = 1)
    {
        $stmt = $this->storage()->prepare($this->toString());

        $this->bindParamsToStmt($stmt);

        $stmt->execute();

        return $stmt->fetchPairs($key, $value);
    }

    /**
     * @param null $value
     * @return self|int
     */
    public function limit($value = null)
    {
        return Obj::fetchIntVar($this, $this->{__FUNCTION__}, func_get_args(), true);
    }

    /**
     * @param null $value
     * @return self|int
     */
    public function offset($value = null)
    {
        return Obj::fetchIntVar($this, $this->{__FUNCTION__}, func_get_args(), true);
    }

    public function __toString()
    {
        return $this->toString();
    }
}