<?php

namespace Greg\Framework\Pagination;

class RangePagination extends BasePagination
{
    private $interval = 2;

    public function startPage()
    {
        $startPage = $this->getPage() - $this->getInterval();

        if ($startPage < 1) {
            $startPage = 1;
        }

        return $startPage;
    }

    public function endPage()
    {
        $endPage = $this->getPage() + $this->getInterval();

        if ($endPage > $this->maxPage()) {
            $endPage = $this->maxPage();
        }

        return $endPage;
    }

    public function prevPageRange()
    {
        if ($this->startPage() > 1) {
            $prevRangePage = $this->startPage() - ($this->getInterval() + 1);

            if ($prevRangePage < 1) {
                $prevRangePage = 1;
            }

            return $prevRangePage;
        }

        return null;
    }

    public function nextPageRange()
    {
        if ($this->endPage() < $this->maxPage()) {
            $nextRangePage = $this->endPage() + ($this->getInterval() + 1);

            if ($nextRangePage > $this->maxPage()) {
                $nextRangePage = $this->maxPage();
            }

            return $nextRangePage;
        }

        return null;
    }

    public function setInterval($number)
    {
        $this->interval = (int) $number;

        return $this;
    }

    public function getInterval()
    {
        return $this->interval;
    }
}
