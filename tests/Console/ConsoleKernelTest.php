<?php

namespace Greg\Framework\Console;

use Greg\Framework\Application;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\NullOutput;

class ConsoleKernelTest extends TestCase
{
    public function testCanInstantiate()
    {
        $kernel = new ConsoleKernel();

        $this->assertInstanceOf(ConsoleKernel::class, $kernel);
    }

    public function testCanGetApplication()
    {
        $kernel = new ConsoleKernel();

        $this->assertInstanceOf(Application::class, $kernel->app());
    }

    public function testCanGetRouter()
    {
        $kernel = new ConsoleKernel();

        $this->assertInstanceOf(\Symfony\Component\Console\Application::class, $kernel->console());
    }

    public function testCanRun()
    {
        $kernel = new ConsoleKernel();

        $response = $kernel->run(null, new NullOutput());

        $this->assertEquals(0, $response);
    }
}
