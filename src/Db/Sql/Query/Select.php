<?php

namespace Greg\Db\Sql\Query;

use Greg\Db\Sql\Query;
use Greg\Db\Sql\QueryTrait;
use Greg\Db\Sql\Query\Traits\From;
use Greg\Db\Sql\Query\Traits\Where;
use Greg\Db\Sql\Table;
use Greg\Engine\Internal;
use Greg\Support\Obj;

/**
 * Class Select
 * @package Greg\Db\Sql\Query
 *
 * @method Select whereCol($column, $operator, $value = null)
 */
class Select
{
    use From, Where, QueryTrait, Internal;

    const ORDER_ASC = 'ASC';

    const ORDER_DESC = 'DESC';

    protected $distinct = false;

    protected $columns = [];

    protected $order = [];

    protected $limit = null;

    protected $offset = null;

    protected $table = null;

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
                throw Exception::newInstance($this->appName(), 'Wrong select order type.');
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
                $cols[] = $this->quoteExpr($column);
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
        return Obj::fetchBoolVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function stmt($execute = true)
    {
        $stmt = $this->storage()->prepare($this->toString());

        $this->bindParamsToStmt($stmt);

        $execute && $stmt->execute();

        return $stmt;
    }

    public function one($column = 0)
    {
        return $this->stmt()->fetchOne($column);
    }

    public function pairs($key = 0, $value = 1)
    {
        return $this->stmt()->fetchPairs($key, $value);
    }

    public function assoc()
    {
        return $this->stmt()->fetchAssoc();
    }

    public function rows()
    {
        $items = $this->assoc();

        $table = $this->table();
        if (!$table) {
            throw Exception::newInstance($this->appName(), 'Undefined table in SELECT query.');
        }

        foreach($items as &$item) {
            $item = $table->createRow($item);
        }
        unset($item);

        $items = $table->createRowSet($items);

        return $items;
    }

    /**
     * @param Table $value
     * @return Table|null|$this
     */
    public function table(Table $value = null)
    {
        return Obj::fetchVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    /**
     * @param null $value
     * @return self|int
     */
    public function limit($value = null)
    {
        return Obj::fetchIntVar($this, $this->{__FUNCTION__}, true, ...func_get_args());
    }

    /**
     * @param null $value
     * @return self|int
     */
    public function offset($value = null)
    {
        return Obj::fetchIntVar($this, $this->{__FUNCTION__}, true, ...func_get_args());
    }

    public function __toString()
    {
        return $this->toString();
    }
}