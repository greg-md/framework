<?php

namespace Greg\Framework\Pagination;

class BasePagination implements \Countable
{
    private $adapter = null;

    public function __construct(PaginationStrategy $adapter)
    {
        $this->adapter = $adapter;
    }

    public function adapter(): PaginationStrategy
    {
        return $this->adapter;
    }

    public function total(): int
    {
        return $this->adapter->paginationTotal();
    }

    public function page(): int
    {
        return $this->adapter->paginationPage();
    }

    public function limit(): int
    {
        return $this->adapter->paginationLimit();
    }

    public function count(): int
    {
        return $this->adapter->paginationCount();
    }

    public function offset(): int
    {
        return ($this->page() - 1) * $this->limit();
    }

    public function maxPage(): int
    {
        $maxPage = 1;

        if (($total = $this->total()) > 0) {
            $maxPage = ceil($total / $this->limit());
        }

        return $maxPage;
    }

    public function prevPage(): int
    {
        $page = $this->page() - 1;

        return $page > 1 ? $page : 1;
    }

    public function nextPage(): int
    {
        $page = $this->page() + 1;

        $maxPage = $this->maxPage();

        return $page > $maxPage ? $maxPage : $page;
    }

    public function hasMorePages(): bool
    {
        return $this->page() < $this->maxPage();
    }
}
