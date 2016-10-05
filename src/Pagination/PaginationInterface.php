<?php

namespace Greg\Pagination;

interface PaginationInterface
{
    public function getPaginationCount();

    public function getPaginationTotal();

    public function getPaginationPage();

    public function getPaginationLimit();
}
