<?php

namespace Greg\Framework;

use Greg\DependencyInjection\IoCContainer;
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

        /** @var ServiceProvider|\PHPUnit_Framework_MockObject_MockObject $bootstrap */
        $bootstrap = $this->getMockBuilder(ServiceProvider::class)->getMock();

        $bootstrap->expects($this->once())->method('boot');

        $app->addServiceProvider($bootstrap);
    }

    public function testCanGetBootstrap()
    {
        $app = new Application();

        /** @var ServiceProvider|\PHPUnit_Framework_MockObject_MockObject $bootstrap */
        $bootstrap = $this->getMockBuilder(ServiceProvider::class)->getMock();

        $app->addServiceProvider($bootstrap);

        $this->assertEquals($bootstrap, $app->getServiceProvider(get_class($bootstrap)));
    }

    public function testCanGetBootstraps()
    {
        $app = new Application();

        /** @var ServiceProvider|\PHPUnit_Framework_MockObject_MockObject $bootstrap */
        $bootstrap = $this->getMockBuilder(ServiceProvider::class)->getMock();

        $app->addServiceProvider($bootstrap);

        $this->assertEquals([get_class($bootstrap) => $bootstrap], $app->getServiceProviders());
    }

    public function testCanListenCallable()
    {
        $app = new Application();

        $success = false;

        $app->listen('event', function ($foo) use (&$success) {
            $success = true;

            $this->assertEquals('foo', $foo);
        });

        $app->fire('event', 'foo');

        $this->assertTrue($success);
    }

    public function testCanListenObject()
    {
        $app = new Application();

        $class = new class($this) {
            public $test;

            public $success = false;

            public function __construct(TestCase $test)
            {
                $this->test = $test;
            }

            public function handle($foo)
            {
                $this->success = true;

                $this->test->assertEquals('foo', $foo);
            }
        };

        $app->listen('event', $class);

        $app->fire('event', 'foo');

        $this->assertTrue($class->success);
    }

    public function testCanThrowExceptionIfUndefinedListener()
    {
        $app = new Application();

        $this->expectException(\Exception::class);

        $app->listen('event', 'undefined_listener');
    }

    public function testCanThrowExceptionIfListenNotHaveHandleMethod()
    {
        $app = new Application();

        $app->listen('event', \stdClass::class);

        $this->expectException(\Exception::class);

        $app->fire('event');
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

    public function testCanScope()
    {
        $app = new Application();

        $app->register($app);

        $success = false;

        $app->scope(function (Application $app) use (&$success) {
            $success = true;

            $this->assertInstanceOf(Application::class, $app);
        });

        $this->assertTrue($success);
    }

    public function testCanRun()
    {
        $app = new Application();

        $success = false;

        $app->run(function () use (&$success) {
            $success = true;
        });

        $this->assertTrue($success);
    }

    public function testCanActApplicationAsArray()
    {
        $app = new Application();

        $app['foo'] = 'bar';

        $this->assertEquals($app['foo'], 'bar');

        $this->assertTrue(isset($app['foo']));

        unset($app['foo']);

        $this->assertFalse(isset($app['foo']));
    }
}
