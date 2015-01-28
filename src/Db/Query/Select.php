<?php

namespace Greg\Db\Query;

use Greg\Db\Query;
use Greg\Db\QueryTrait;
use Greg\Db\Query\Traits\From;
use Greg\Db\Query\Traits\Where;
use Greg\Engine\Internal;
use Greg\Support\Obj;

class Select
{
    use From, Where, QueryTrait, Internal;

    protected $distinct = false;

    protected $columns = [];

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

        $from = $this->fromToString();
        if ($from) {
            $query[] = $from;
        }

        $where = $this->whereToString();
        if ($where) {
            $query[] = $where;
        }

        return implode(' ', $query);
    }

    public function distinct($value = null)
    {
        return Obj::fetchBoolVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function fetchOne($column = 0)
    {
        $stmt = $this->storage()->prepare($this->toString());

        $this->bindParamsToStmt($stmt);

        $stmt->execute();

        return $stmt->fetchOne($column);
    }

    public function __toString()
    {
        return $this->toString();
    }
}