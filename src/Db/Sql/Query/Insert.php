<?php

namespace Greg\Db\Sql\Query;

use Greg\Db\Sql\QueryTrait;
use Greg\Engine\Internal;
use Greg\Engine\InternalInterface;
use Greg\Support\Obj;

class Insert implements InternalInterface
{
    use QueryTrait, Internal;

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

    public function columns($key = null, $value = null, $type = Obj::VAR_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function values($key = null, $value = null, $type = Obj::VAR_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function select($value = null, $type = Obj::VAR_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function toString()
    {
        $query = [
            'INSERT INTO',
        ];

        $into = $this->into();
        if (!$into) {
            throw Exception::create($this->appName(), 'Undefined insert table.');
        }

        $query[] = $into;

        $columns = $this->columns();

        $quoteColumns = array_map(function($column) {
            return $this->quoteNamedExpr($column);
        }, $columns);

        if (!$quoteColumns) {
            throw Exception::create($this->appName(), 'Undefined insert columns.');
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
}