<?php

namespace Greg\Framework\Http;

use Greg\Support\Http\Request;
use Greg\Support\Http\Response;
use PHPUnit\Framework\TestCase;

class Controller
{
    use HttpControllerTrait;

    public function returnResponse()
    {
        return $this->response();
    }

    public function returnLocation(string $location = null)
    {
        return $this->location($location);
    }

    public function returnRefresh()
    {
        return $this->refresh();
    }

    public function returnBack()
    {
        return $this->back();
    }

    public function returnJson($data)
    {
        return $this->json($data);
    }

    public function returnDownload(string $content, string $name = null, string $type = null)
    {
        return $this->download($content, $name, $type);
    }
}

class HttpControllerTraitTest extends TestCase
{
    public function testCanReturnResponse()
    {
        $controller = new Controller();

        $this->assertInstanceOf(Response::class, $controller->returnResponse());
    }

    public function testCanReturnLocation()
    {
        $controller = new Controller();

        $response = $controller->returnLocation($location = '/location');

        $this->assertEquals($location, $response->getLocation());
    }

    public function testCanReturnRefresh()
    {
        $controller = new Controller();

        $response = $controller->returnRefresh();

        $this->assertEquals(Request::uri(), $response->getLocation());
    }

    public function testCanReturnBack()
    {
        $controller = new Controller();

        $response = $controller->returnBack();

        $this->assertEquals(Request::uri(), $response->getLocation());
    }

    public function testCanReturnJson()
    {
        $controller = new Controller();

        $response = $controller->returnJson(true);

        $this->assertEquals('true', $response->getContent());
    }

    public function testCanReturnDownload()
    {
        $controller = new Controller();

        $response = $controller->returnDownload('Hello World!', 'hello.world', 'text/plain');

        $this->assertEquals('Hello World!', $response->getContent());

        $this->assertEquals('hello.world', $response->getFileName());

        $this->assertEquals('text/plain', $response->getContentType());

        $this->assertEquals('download', $response->getDisposition());
    }
}
