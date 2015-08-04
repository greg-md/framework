<?php

namespace Greg\Support\Db\Sql\Query;

use Greg\Support\Db\Sql\Query;
use Greg\Support\Tool\Debug;
use Greg\Support\Tool\Obj;

class Insert extends Query
{
    protected $into = null;

    protected $columns = [];

    protected $values = [];

    protected $select = null;

    public function into($name = null)
    {
        if (func_num_args()) {
            $this->into = $name;

            return $this;
        }

        return $this->into;
    }

    /**
     * @param $data
     * @return Insert
     */
    public function data($data)
    {
        $this->columns(array_keys($data), true);

        $this->values($data, true);

        return $this;
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
            'INSERT INTO',
        ];

        $into = $this->into();
        if (!$into) {
            throw new \Exception('Undefined insert table.');
        }

        list($intoAlias, $intoName) = $this->fetchAlias($into);

        unset($intoAlias);

        $query[] = $this->quoteNamedExpr($intoName);

        $columns = $this->columns();

        $quoteColumns = array_map(function($column) {
            return $this->quoteNamedExpr($column);
        }, $columns);

        if (!$quoteColumns) {
            throw new \Exception('Undefined insert columns.');
        }

        $query[] = '(' . implode(', ', $quoteColumns) . ')';

        $select = $this->select();

        if ($select) {
            $query[] = $select;
        } else {
            $values = [];
            foreach($columns as $column) {
                $values[] = $this->values($column);
            }
            $this->bindParams($values);

            $query[] = 'VALUES';

            $query[] = '(' . implode(', ', str_split(str_repeat('?', sizeof($columns)))) . ')';
        }

        return implode(' ', $query);
    }

    public function __toString()
    {
        return $this->toString();
    }

    public function columns($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function values($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function select($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function __debugInfo()
    {
        return Debug::fixInfo($this, get_object_vars($this), false);
    }
}