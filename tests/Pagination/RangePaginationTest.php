<?php

namespace Greg\Framework\Pagination;

use PHPUnit\Framework\TestCase;

class RangePaginationTest extends TestCase
{
    public function testCanInstantiate()
    {
        $pagination = new RangePagination($strategy = $this->mockStrategy());

        $this->assertInstanceOf(BasePagination::class, $pagination);
    }

    public function testCanInstantiateWithInterval()
    {
        $pagination = new RangePagination($strategy = $this->mockStrategy(), 10);

        $this->assertEquals(10, $pagination->interval());
    }

    public function testCanGetStartPage()
    {
        $pagination = new RangePagination($strategy = $this->mockStrategy());

        $strategy->method('paginationPage')->willReturnOnConsecutiveCalls(1, 10);

        $this->assertEquals(1, $pagination->startPage());

        $this->assertEquals(8, $pagination->startPage());
    }

    public function testCanGetEndPage()
    {
        $pagination = new RangePagination($strategy = $this->mockStrategy());

        $strategy->method('paginationTotal')->willReturn(100);

        $strategy->method('paginationPage')->willReturnOnConsecutiveCalls(5, 10);

        $strategy->method('paginationLimit')->willReturn(10);

        $this->assertEquals(7, $pagination->endPage());

        $this->assertEquals(10, $pagination->endPage());
    }

    public function testCanGetPreviousPageRange()
    {
        $pagination = new RangePagination($strategy = $this->mockStrategy());

        $strategy->method('paginationPage')->willReturnOnConsecutiveCalls(1, 5, 10);

        $this->assertNull($pagination->prevPageRange());

        $this->assertEquals(1, $pagination->prevPageRange());

        $this->assertEquals(5, $pagination->prevPageRange());
    }

    public function testCanGetNextPageRange()
    {
        $pagination = new RangePagination($strategy = $this->mockStrategy());

        $strategy->method('paginationTotal')->willReturn(100);

        $strategy->method('paginationPage')->willReturnOnConsecutiveCalls(2, 7, 10);

        $strategy->method('paginationLimit')->willReturn(10);

        $this->assertEquals(7, $pagination->nextPageRange());

        $this->assertEquals(10, $pagination->nextPageRange());

        $this->assertNull($pagination->nextPageRange());
    }

    private function mockStrategy()
    {
        return $this->getMockBuilder(PaginationStrategy::class)->getMock();
    }
}
