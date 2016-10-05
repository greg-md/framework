<?php

namespace Greg\Pagination;

class BasePagination implements \Countable
{
    /**
     * @var PaginationInterface|null
     */
    protected $adapter = null;

    public function setAdapter(PaginationInterface $adapter)
    {
        $this->adapter = $adapter;

        return $this;
    }

    public function getAdapter()
    {
        if (!$this->adapter) {
            throw new \Exception('Pagination adapter is not defined.');
        }

        return $this->adapter;
    }

    public function count()
    {
        return $this->getAdapter()->getPaginationCount();
    }

    public function offset()
    {
        return ($this->getPage() - 1) * $this->getLimit();
    }

    public function maxPage()
    {
        $maxPage = 1;

        if (($total = $this->getTotal()) > 0) {
            $maxPage = ceil($total / $this->getLimit());
        }

        return $maxPage;
    }

    public function prevPage()
    {
        $page = $this->getPage() - 1;

        return $page > 1 ? $page : 1;
    }

    public function nextPage()
    {
        $page = $this->getPage() + 1;

        $maxPage = $this->maxPage();

        return $page > $maxPage ? $maxPage : $page;
    }

    public function hasMorePages()
    {
        return $this->getPage() < $this->maxPage();
    }

    public function getTotal()
    {
        return $this->getAdapter()->getPaginationTotal();
    }

    public function getPage()
    {
        return $this->getAdapter()->getPaginationPage();
    }

    public function getLimit()
    {
        return $this->getAdapter()->getPaginationLimit();
    }
}
