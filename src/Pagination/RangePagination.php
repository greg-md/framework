<?php

namespace Greg\Framework\Pagination;

class RangePagination extends BasePagination
{
    private $interval;

    public function __construct(PaginationStrategy $adapter, int $interval = 2)
    {
        $this->interval = $interval;

        return parent::__construct($adapter);
    }

    public function interval(): int
    {
        return $this->interval;
    }

    public function startPage(): int
    {
        $startPage = $this->page() - $this->interval;

        if ($startPage < 1) {
            $startPage = 1;
        }

        return $startPage;
    }

    public function endPage(): int
    {
        $endPage = $this->page() + $this->interval;

        if ($endPage > $this->maxPage()) {
            $endPage = $this->maxPage();
        }

        return $endPage;
    }

    public function prevPageRange(): ?int
    {
        if (($startPage = $this->startPage()) > 1) {
            $prevRangePage = $startPage - ($this->interval + 1);

            if ($prevRangePage < 1) {
                $prevRangePage = 1;
            }

            return $prevRangePage;
        }

        return null;
    }

    public function nextPageRange(): ?int
    {
        $endPage = $this->endPage();
        $maxPage = $this->maxPage();

        if ($endPage < $maxPage) {
            $nextRangePage = $endPage + ($this->interval + 1);

            if ($nextRangePage > $maxPage) {
                $nextRangePage = $maxPage;
            }

            return $nextRangePage;
        }

        return null;
    }
}
