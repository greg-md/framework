<?php

namespace Greg\Framework\Pagination;

use PHPUnit\Framework\TestCase;

class BasePaginationTest extends TestCase
{
    public function testCanInstantiate()
    {
        $pagination = new BasePagination();

        $this->assertInstanceOf(BasePagination::class, $pagination);
    }
}