<?php

namespace Greg\Service;

use Greg\Engine\InternalTrait;
use Greg\Storage\AccessorTrait;
use Greg\Storage\ArrayAccessTrait;
use Greg\Storage\CountableTrait;
use Greg\Tool\Obj;

class PaginationService implements \ArrayAccess, \Countable
{
    use InternalTrait, AccessorTrait, ArrayAccessTrait, CountableTrait;

    protected $total = 0;

    protected $page = 1;

    protected $limit = 10;

    public function __construct($page = null, $limit = null)
    {
        if ($page !== null) {
            $this->page($page);
        }

        if ($limit !== null) {
            $this->limit($limit);
        }
    }

    public function items(array $items = null)
    {
        if ($items === null) {
            return $this->storage;
        }

        $this->storage = $items;

        return $this;
    }

    public function offset()
    {
        return ($this->page() - 1) * $this->limit();
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