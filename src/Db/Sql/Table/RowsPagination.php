<?php

namespace Greg\Db\Sql\Table;

use Greg\Db\Sql\Table;
use Greg\Support\Obj;

class RowsPagination extends Rows
{
    protected $total = 0;

    protected $page = 1;

    protected $limit = 10;

    public function __construct(Table $table, $items = [], $total = null, $page = null, $limit = null)
    {
        if ($total !== null) {
            $this->total($total);
        }

        if ($page !== null) {
            $this->page($page);
        }

        if ($limit !== null) {
            $this->limit($limit);
        }

        return parent::__construct($table, $items);
    }

    static public function create($appName, $tableName, $items = [], $total = null, $page = null, $limit = null)
    {
        return static::newInstanceRef($appName, $tableName, $items, $total, $page, $limit);
    }

    public function maxPage()
    {
        $maxPage = 1;

        if (($total = $this->total()) > 0) {
            $maxPage = ceil($total / $this->limit());
        }

        return $maxPage;
    }

    public function prevPage()
    {
        $page = $this->page() - 1;

        return $page > 1 ? $page : 1;
    }

    public function nextPage()
    {
        $page = $this->page() + 1;

        $maxPage = $this->maxPage();

        return $page > $maxPage ? $maxPage : $page;
    }

    public function hasMorePages()
    {
        return $this->page() < $this->maxPage();
    }

    public function total($value = null)
    {
        return Obj::fetchIntVar($this, $this->{__FUNCTION__}, true, ...func_get_args());
    }

    public function page($value = null)
    {
        return Obj::fetchIntVar($this, $this->{__FUNCTION__}, true, ...func_get_args());
    }

    public function limit($value = null)
    {
        return Obj::fetchIntVar($this, $this->{__FUNCTION__}, true, ...func_get_args());
    }
}