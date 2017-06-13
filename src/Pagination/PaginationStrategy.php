<?php

namespace Greg\Framework\Pagination;

interface PaginationStrategy
{
    public function getPaginationCount();

    public function getPaginationTotal();

    public function getPaginationPage();

    public function getPaginationLimit();
}
