<?php

namespace Greg\Framework;

use PHPUnit\Framework\TestCase;

class ApplicationTest extends TestCase
{
    public function testCanCreateNewInstance()
    {
        $app = new Application();

        $this->assertInstanceOf(Application::class, $app);
    }

    public function testCanGetConfig()
    {
        $app = new Application();

        $this->assertInstanceOf(Config::class, $app->config());
    }

    public function testCanGetIoCContainer()
    {
        $app = new Application();

        $this->assertInstanceOf(IoCContainer::class, $app->ioc());
    }

    public function testCanAddBootstrap()
    {
        $app = new Application();

        /** @var BootstrapInterface|\PHPUnit_Framework_MockObject_MockObject $bootstrap */
        $bootstrap = $this->getMockBuilder(BootstrapInterface::class)->getMock();

        $bootstrap->expects($this->once())->method('boot');

        $app->bootstrap($bootstrap);
    }

    public function testCanGetBootstrap()
    {
        $app = new Application();

        /** @var BootstrapInterface|\PHPUnit_Framework_MockObject_MockObject $bootstrap */
        $bootstrap = $this->getMockBuilder(BootstrapInterface::class)->getMock();

        $app->bootstrap($bootstrap);

        $this->assertEquals($bootstrap, $app->getBootstrap(get_class($bootstrap)));
    }

    public function testCanGetBootstraps()
    {
        $app = new Application();

        /** @var BootstrapInterface|\PHPUnit_Framework_MockObject_MockObject $bootstrap */
        $bootstrap = $this->getMockBuilder(BootstrapInterface::class)->getMock();

        $app->bootstrap($bootstrap);

        $this->assertEquals([get_class($bootstrap) => $bootstrap], $app->getBootstraps());
    }

    public function testCanFireEvents()
    {
        $app = new Application();

        $success = false;

        $app->listen('event', function ($foo) use (&$success) {
            $success = true;

            $this->assertEquals('foo', $foo);
        });

        $app->fire('event', ...['foo']);

        $this->assertTrue($success);
    }

    public function testCanRunEvent()
    {
        $app = new Application();

        $success = false;

        $event = new class() {
        };

        $app->listen(get_class($event), function ($arg) use ($event, &$success) {
            $success = true;

            $this->assertInstanceOf(get_class($event), $arg);
        });

        $app->event($event);

        $this->assertTrue($success);
    }

    public function testCanThrowExceptionIfEventIsNotAnObject()
    {
        $app = new Application();

        $this->expectException(\Exception::class);

        $app->event('test');
    }
}
