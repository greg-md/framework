<?php

namespace Greg\Framework\Pagination;

interface PaginationStrategy
{
    public function paginationTotal(): int;

    public function paginationPage(): int;

    public function paginationLimit(): int;

    public function paginationCount(): int;
}
