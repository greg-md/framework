<?php

namespace Greg\Framework\Http;

use Greg\Framework\Application;
use Greg\Framework\KernelStrategy;
use Greg\Routing\Route;
use Greg\Routing\Router;
use Greg\Support\Http\Request;
use Greg\Support\Http\Response;
use Greg\Support\Obj;

class HttpKernel implements KernelStrategy
{
    const EVENT_RUN = 'http.run';

    const EVENT_DISPATCHING = 'http.dispatching';

    const EVENT_DISPATCHED = 'http.dispatched';

    const EVENT_FINISHED = 'http.finished';

    private $app = null;

    private $router = null;

    private $controllersPrefixes = [];

    private $loadedControllers = [];

    public function __construct(Application $app, Router $router = null)
    {
        $this->app = $app;

        $this->router = $router ?: new Router();

        $this->addDispatcherToRouter();

        $this->boot();

        return $this;
    }

    public function app(): Application
    {
        return $this->app;
    }

    public function router(): Router
    {
        return $this->router;
    }

    public function addControllersPrefix($prefix)
    {
        $this->controllersPrefixes[] = (string) $prefix;

        $this->fixControllersPrefixes();

        return $this;
    }

    public function addControllersPrefixes(array $prefixes)
    {
        $this->controllersPrefixes = array_merge($this->controllersPrefixes, $prefixes);

        $this->fixControllersPrefixes();

        return $this;
    }

    public function setControllersPrefixes(array $prefixes)
    {
        $this->controllersPrefixes = $prefixes;

        $this->fixControllersPrefixes();

        return $this;
    }

    public function getControllersPrefixes()
    {
        return $this->controllersPrefixes;
    }

    public function run(?string $path = null, ?string $method = null)
    {
        $path = $path ?: Request::relativeUriPath();

        $method = $method ?: Request::method();

        $this->app->fire(static::EVENT_RUN, $path);

        if ($route = $this->router->detect($path, $method, $data)) {
            $this->app->fire(static::EVENT_DISPATCHING, $path, $route);

            $response = $route->exec($data);

            if (!($response instanceof Response)) {
                $response = new Response($response);
            }

            $this->app->fire(static::EVENT_DISPATCHED, $path, $route, $response);
        } else {
            $response = new Response('Route not found.', null, 404);
        }

        $this->app->fire(static::EVENT_FINISHED, $path, $response);

        return $response;
    }

    protected function boot()
    {
    }

    protected function addDispatcherToRouter()
    {
        $this->router->setDispatcher(function ($action) {
            $parts = explode('@', $action, 2);

            if (!isset($parts[1])) {
                throw new \Exception('Action name is not defined in router controller `' . $parts[0] . '`.');
            }

            list($controllerName, $actionName) = $parts;

            $controller = $this->getController($controllerName);

            if (!method_exists($controller, $actionName)) {
                throw new \Exception('Action `' . $actionName . '` does not exists in controller `' . $controllerName . '`.');
            }

            $action = [$controller, $actionName];

            return function (Route $route, ...$params) use ($action) {
                return $this->app->ioc()->call($action, $route, ...$params);
            };
        });

        return $this;
    }

    protected function getController($name)
    {
        if (!$className = $this->controllerExists($name)) {
            throw new \Exception('Controller `' . $name . '` not found.');
        }

        if (!array_key_exists($className, $this->loadedControllers)) {
            $class = $this->app->ioc()->load($className);

            $this->loadedControllers[$className] = $class;
        }

        return $this->loadedControllers[$className];
    }

    protected function controllerExists($name)
    {
        return Obj::exists($name, array_merge($this->controllersPrefixes, ['']));
    }

    protected function fixControllersPrefixes()
    {
        $this->controllersPrefixes = array_unique($this->controllersPrefixes);

        return $this;
    }
}
