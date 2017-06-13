<?php

namespace Greg\Framework\Http;

use Greg\Framework\Application;
use Greg\Routing\Router;
use Greg\Support\Http\Response;
use PHPUnit\Framework\TestCase;

class HttpKernelTest extends TestCase
{
    public function testCanInstantiate()
    {
        $kernel = new HttpKernel();

        $this->assertInstanceOf(HttpKernel::class, $kernel);
    }

    public function testCanGetApplication()
    {
        $kernel = new HttpKernel();

        $this->assertInstanceOf(Application::class, $kernel->app());
    }

    public function testCanGetRouter()
    {
        $kernel = new HttpKernel();

        $this->assertInstanceOf(Router::class, $kernel->router());
    }

    public function testCanAddControllersPrefixes()
    {
        $kernel = new HttpKernel();

        $kernel->addControllersPrefixes('Foo\\', 'Bar\\');

        $kernel->addControllersPrefixes('Baz\\');

        $this->assertEquals(['Foo\\', 'Bar\\', 'Baz\\'], $kernel->getControllersPrefixes());
    }

    public function testCanSetControllersPrefixes()
    {
        $kernel = new HttpKernel();

        $kernel->setControllersPrefixes('Foo\\', 'Bar\\');

        $kernel->setControllersPrefixes('Baz\\');

        $this->assertEquals(['Baz\\'], $kernel->getControllersPrefixes());
    }

    public function testCanRunAndReturnNotFound()
    {
        $kernel = new HttpKernel();

        $response = $kernel->run();

        $this->assertInstanceOf(Response::class, $response);

        $this->assertEquals(404, $response->getCode());
    }

    public function testCanRunAndFindRouter()
    {
        $kernel = new HttpKernel();

        $kernel->router()->any('/', function () {
            return 'Hello World!';
        });

        $response = $kernel->run('/');

        $this->assertEquals('Hello World!', $response->getContent());
    }

    public function testCanRunAnPrefixedControllerAction()
    {
        $kernel = new HttpKernel();

        $kernel->addControllersPrefixes(__NAMESPACE__ . '\\');

        $kernel->router()->any('/', 'FooController@fooAction');

        $response = $kernel->run('/');

        $this->assertEquals('Hello World!', $response->getContent());
    }

    public function testCanThrowExceptionIfControllerActionIsNotFefined()
    {
        $kernel = new HttpKernel();

        $kernel->addControllersPrefixes(__NAMESPACE__ . '\\');

        $kernel->router()->any('/', 'FooController');

        $this->expectException(\Exception::class);

        $kernel->run('/');
    }

    public function testCanThrowExceptionIfControllerActionNotFound()
    {
        $kernel = new HttpKernel();

        $kernel->addControllersPrefixes(__NAMESPACE__ . '\\');

        $kernel->router()->any('/', 'FooController@undefinedAction');

        $this->expectException(\Exception::class);

        $kernel->run('/');
    }

    public function testCanThrowExceptionIfControllerNotFound()
    {
        $kernel = new HttpKernel();

        $kernel->addControllersPrefixes(__NAMESPACE__ . '\\');

        $kernel->router()->any('/', 'UndefinedController@action');

        $this->expectException(\Exception::class);

        $kernel->run('/');
    }
}

class FooController
{
    public function fooAction()
    {
        return 'Hello World!';
    }
}
