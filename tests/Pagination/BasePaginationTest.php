<?php

namespace Greg\Framework\Pagination;

use PHPUnit\Framework\TestCase;

class BasePaginationTest extends TestCase
{
    public function testCanInstantiate()
    {
        $pagination = new BasePagination($strategy = $this->mockStrategy());

        $this->assertInstanceOf(BasePagination::class, $pagination);
    }

    public function testCanGetAdapter()
    {
        $pagination = new BasePagination($strategy = $this->mockStrategy());

        $this->assertInstanceOf(PaginationStrategy::class, $pagination->adapter());
    }

    public function testCanGetTotal()
    {
        $pagination = new BasePagination($strategy = $this->mockStrategy());

        $strategy->method('paginationTotal')->willReturn(10);

        $this->assertEquals(10, $pagination->total());
    }

    public function testCanGetPage()
    {
        $pagination = new BasePagination($strategy = $this->mockStrategy());

        $strategy->method('paginationPage')->willReturn(10);

        $this->assertEquals(10, $pagination->page());
    }

    public function testCanGetLimit()
    {
        $pagination = new BasePagination($strategy = $this->mockStrategy());

        $strategy->method('paginationLimit')->willReturn(10);

        $this->assertEquals(10, $pagination->limit());
    }

    public function testCanGetCount()
    {
        $pagination = new BasePagination($strategy = $this->mockStrategy());

        $strategy->method('paginationCount')->willReturn(10);

        $this->assertEquals(10, $pagination->count());
    }

    public function testCanGetOffset()
    {
        $pagination = new BasePagination($strategy = $this->mockStrategy());

        $strategy->method('paginationPage')->willReturn(2);

        $strategy->method('paginationLimit')->willReturn(10);

        $this->assertEquals(10, $pagination->offset());
    }

    public function testCanGetMaxPage()
    {
        $pagination = new BasePagination($strategy = $this->mockStrategy());

        $strategy->method('paginationTotal')->willReturn(100);

        $strategy->method('paginationLimit')->willReturn(10);

        $this->assertEquals(10, $pagination->maxPage());
    }

    public function testCanGetPreviousPage()
    {
        $pagination = new BasePagination($strategy = $this->mockStrategy());

        $strategy->method('paginationPage')->willReturn(11);

        $this->assertEquals(10, $pagination->prevPage());
    }

    public function testCanGetNextPage()
    {
        $pagination = new BasePagination($strategy = $this->mockStrategy());

        $strategy->method('paginationTotal')->willReturn(100);

        $strategy->method('paginationPage')->willReturn(9);

        $strategy->method('paginationLimit')->willReturn(10);

        $this->assertEquals(10, $pagination->nextPage());
    }

    public function testCanDetermineIfHasMorePages()
    {
        $pagination = new BasePagination($strategy = $this->mockStrategy());

        $strategy->method('paginationTotal')->willReturn(100);

        $strategy->method('paginationPage')->willReturnOnConsecutiveCalls(9, 10);

        $strategy->method('paginationLimit')->willReturn(10);

        $this->assertTrue($pagination->hasMorePages());

        $this->assertFalse($pagination->hasMorePages());
    }

    private function mockStrategy()
    {
        return $this->getMockBuilder(PaginationStrategy::class)->getMock();
    }
}
