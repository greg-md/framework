<?php

namespace Greg\Db\Sql\Query;

use Greg\Db\Sql\Query;
use Greg\Db\Sql\Table;
use Greg\Support\Debug;
use Greg\Support\Obj;

/**
 * Class Select
 * @package Greg\Db\Sql\Query
 *
 * @method Select whereRel($column, $operator, $value = null)
 * @method Select whereCol($column, $operator, $value = null)
 * @method Select whereCols(array $columns)
 * @method Select where($expr = null, $value = null, $_ = null)
 * @method Select isNull($column)
 * @method Select isNotNull($column)
 */
class Select extends Query
{
    use FromTrait, WhereTrait;

    const ORDER_ASC = 'ASC';

    const ORDER_DESC = 'DESC';

    protected $distinct = false;

    protected $columns = [];

    protected $group = [];

    protected $order = [];

    protected $limit = null;

    protected $offset = null;

    protected $table = null;

    public function only($column = null, $_ = null)
    {
        if (!is_array($column)) {
            $column = func_get_args();
        }

        return $this->tableColumns($this->getTable(), $column);
    }

    /**
     * @param null $table
     * @param null $column
     * @param null $_
     * @return Select|array
     */
    public function from($table = null, $column = null, $_ = null)
    {
        if (func_num_args()) {
            $this->from[] = $table;

            if (!is_array($column)) {
                $column = func_get_args();

                array_shift($column);
            }

            if ($column) {
                $this->tableColumns($table, $column);
            }

            return $this;
        }

        return $this->from;
    }

    public function tableColumns($table, $column, $_ = null)
    {
        if (!is_array($column)) {
            $column = func_get_args();
        }

        list($alias, $name) = $this->fetchAlias($table);

        if (!$alias) {
            $alias = $name;
        }

        foreach($column as &$col) {
            if ($this->isCleanColumn($col)) {
                $col = $alias . '.' . $col;
            }
        }
        unset($col);

        $this->columns($column);

        return $this;
    }

    public function column($column, $alias = null)
    {
        if ($column instanceof \Closure) {
            $column = $this->app()->binder()->call($column);
        }

        $this->columns[] = $alias !== null ? [$alias => $column] : $column;

        return $this;
    }

    public function columns($column = null, $_ = null)
    {
        if (func_num_args()) {

            if (!is_array($column)) {
                $column = func_get_args();
            }

            array_map([$this, 'column'], $column);

            return $this;
        }

        return $this->columns;
    }

    public function clearColumns()
    {
        $this->columns = [];

        return $this;
    }

    /**
     * @param null $expr
     * @return Select|array
     */
    public function group($expr = null)
    {
        if (func_num_args()) {
            $this->group[] = $expr;

            return $this;
        }

        return $this->group;
    }

    public function hasGroup()
    {
        return (bool)$this->group;
    }

    public function clearGroup()
    {
        $this->group = [];

        return $this;
    }

    /**
     * @param null $expr
     * @param null $type
     * @return Select|array
     * @throws \Exception
     */
    public function order($expr = null, $type = null)
    {
        if (func_num_args()) {
            if ($type and !in_array($type, [static::ORDER_ASC, static::ORDER_DESC])) {
                throw new \Exception('Wrong select order type.');
            }

            $this->order[] = [
                'expr' => $expr,
                'type' => $type,
            ];

            return $this;
        }

        return $this->order;
    }

    public function hasOrder()
    {
        return (bool)$this->order;
    }

    public function clearOrder()
    {
        $this->order = [];

        return $this;
    }

    public function groupToString()
    {
        $group = [];

        foreach($this->group as $expr) {
            $group[] = $this->quoteExpr($expr);
        }

        return $group ? 'GROUP BY ' . implode(', ', $group) : '';
    }

    public function orderToString()
    {
        $order = [];

        foreach($this->order as $info) {
            $order[] = $this->quoteExpr($info['expr']) . ($info['type'] ? ' ' . $info['type'] : '');
        }

        return $order ? 'ORDER BY ' . implode(', ', $order) : '';
    }

    public function selectToString()
    {
        $query = ['SELECT'];

        if ($this->distinct()) {
            $query[] = 'DISTINCT';
        }

        $columns = $this->columns();

        if ($columns) {
            $cols = [];

            foreach($columns as $column) {
                $cols[] = $this->quoteAliasExpr($column);

                list($alias, $expr) = $this->fetchAlias($column);

                unset($alias);

                if ($expr instanceof Query) {
                    $this->bindParams($expr->bindParams());
                }
            }

            $query[] = implode(', ', $cols);
        } else {
            $query[] = '*';
        }

        return implode(' ', $query);
    }

    public function toString()
    {
        $this->clearBindParams();

        $query = [];

        if ($select = $this->selectToString()) {
            $query[] = $select;
        }

        if ($from = $this->fromToString()) {
            $query[] = $from;
        }

        if ($where = $this->whereToString()) {
            $query[] = $where;
        }

        if ($group = $this->groupToString()) {
            $query[] = $group;
        }

        if ($order = $this->orderToString()) {
            $query[] = $order;
        }

        if (method_exists($this, 'parseLimit')) {
            $this->parseLimit($query);
        }

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

    public function exists()
    {
        return (bool)$this->one();
    }

    public function pairs($key = 0, $value = 1)
    {
        return $this->stmt()->fetchPairs($key, $value);
    }

    public function assoc()
    {
        return $this->stmt()->fetchAssoc();
    }

    public function assocFull($references = null, $relationships = null, $dependencies = '*')
    {
        $item = $this->assoc();

        $items = [$item];

        $this->getTable()->addFullInfo($items, $references, $relationships, $dependencies);

        return $items[0];
    }

    public function assocAll()
    {
        return $this->stmt()->fetchAssocAll();
    }

    public function assocAllFull($references = null, $relationships = null, $dependencies = '*')
    {
        $items = $this->assocAll();

        $this->getTable()->addFullInfo($items, $references, $relationships, $dependencies);

        return $items;
    }

    public function row()
    {
        return ($row = $this->assoc()) ? $this->getTable()->createRow($row) : null;
    }


    public function rowFull($references = null, $relationships = null, $dependencies = '*')
    {
        $item = $this->assoc();

        if ($item) {
            $items = [$item];

            $this->getTable()->addFullInfo($items, $references, $relationships, $dependencies, true);

            $item = $items[0];
        }

        return $item;
    }

    public function rows()
    {
        $table = $this->getTable();

        $items = $this->assocAll();

        foreach($items as &$item) {
            $item = $table->createRow($item);
        }
        unset($item);

        $items = $table->createRows($items);

        return $items;
    }

    public function rowsFull($references = null, $relationships = null, $dependencies = '*')
    {
        $items = $this->assocAll();

        $table = $this->getTable();

        $table->addFullInfo($items, $references, $relationships, $dependencies, true);

        $items = $table->createRows($items);

        return $items;
    }

    public function paginationAssoc($page = 1, $limit = 10)
    {
        if ($page < 1) {
            $page = 1;
        }

        if ($limit < 1) {
            $limit = 10;
        }

        $countQ = clone $this;

        $countQ->clearColumns();

        $countQ->clearOrder();

        if ($countQ->hasGroup()) {
            $storage = $this->getTable()->storage();

            $countQ->columns($storage->expr('1'));

            $countQ = $storage->select('count(*)')->from([uniqid('table_') => $countQ]);
        } else {
            $countQ->columns('count(*)');

            if (!$countQ->hasWhere()) {
                $countQ->clearJoinLeft();

                $countQ->clearJoinRight();
            }
        }

        $maxPage = 1;

        $total = $countQ->one();

        if ($total > 0) {
            $maxPage = ceil($total / $limit);

            if ($page > $maxPage) {
                $page = $maxPage;
            }
        }

        $query = clone $this;

        $query->limit($limit)->offset(($page - 1) * $limit);

        $items = $query->assocAll();

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'maxPage' => $maxPage,
        ];
    }

    public function paginationAssocFull($page = 1, $limit = 10, $references = null, $relationships = null, $dependencies = '*')
    {
        $pagination = $this->paginationAssoc($page, $limit);

        $this->getTable()->addFullInfo($pagination['items'], $references, $relationships, $dependencies);

        return $pagination;
    }

    public function pagination($page = 1, $limit = 10)
    {
        $pagination = $this->paginationAssoc($page, $limit);

        $table = $this->getTable();

        foreach($pagination['items'] as &$item) {
            $item = $table->createRow($item);
        }
        unset($item);

        return $table->createRowsPagination($pagination['items'], $pagination['total'], $pagination['page'], $pagination['limit']);
    }

    public function paginationFull($page = 1, $limit = 10, $references = null, $relationships = null, $dependencies = '*')
    {
        $pagination = $this->paginationAssoc($page, $limit);

        $table = $this->getTable();

        $table->addFullInfo($pagination['items'], $references, $relationships, $dependencies, true);

        return $table->createRowsPagination($pagination['items'], $pagination['total'], $pagination['page'], $pagination['limit']);
    }

    public function getTable()
    {
        $table = $this->table();

        if (!$table) {
            throw new \Exception('Undefined table in Select query.');
        }

        return $table;
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

    public function __debugInfo()
    {
        return Debug::fixInfo($this, get_object_vars($this), false);
    }
}