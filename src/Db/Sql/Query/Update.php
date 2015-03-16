<?php

namespace Greg\Db\Sql\Query;

use Greg\Db\Sql\Query\Traits\Where;
use Greg\Db\Sql\QueryTrait;
use Greg\Engine\Internal;

/**
 * Class Update
 * @package Greg\Db\Sql\Query
 *
 * @method Update whereCol($column, $value = null, $operator = '=')
 */
class Update
{
    use Where, QueryTrait, Internal;

    protected $tables = [];

    protected $set = [];

    public function table($table)
    {
        $this->tables[] = $table;

        return $this;
    }

    public function set(array $values = [])
    {
        if (func_num_args()) {
            $this->set = array_merge($this->set, $values);

            return $this;
        }

        return $this->set;
    }

    public function tables($tables = null, $_ = null)
    {
        if (func_num_args()) {

            if (!is_array($tables)) {
                $tables = func_get_args();
            }

            $this->tables = array_merge($this->tables, $tables);

            return $this;
        }

        return $this->tables;
    }

    public function exec()
    {
        $stmt = $this->storage()->prepare($this->toString());

        $this->bindParamsToStmt($stmt);

        return $stmt->execute();
    }

    public function toString()
    {
        $query = [
            'UPDATE',
        ];

        if (!$this->tables) {
            throw Exception::newInstance($this->appName(), 'Undefined update tables.');
        }

        $tables = [];

        foreach($this->tables as $name) {
            $tables[] = $this->quoteAliasExpr($name);
        }

        $query[] = implode(', ', $tables);

        if (!$this->set) {
            throw Exception::newInstance($this->appName(), 'Undefined update set.');
        }

        $query[] = 'SET';

        $query[] = implode(', ', array_map(function($expr) {
            return $this->quoteName($expr) . ' = ?';
        }, array_keys($this->set)));

        $this->bindParams(array_values($this->set));

        $where = $this->whereToString();

        if ($where) {
            $query[] = $where;
        }

        return implode(' ', $query);
    }

    public function __toString()
    {
        return $this->toString();
    }
}