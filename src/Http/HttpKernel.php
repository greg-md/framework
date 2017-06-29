<?php

namespace Greg\Framework\Http;

use Greg\Framework\Application;
use Greg\Framework\ServiceProvider;
use Greg\Routing\Router;
use Greg\Support\Http\Request;
use Greg\Support\Http\Response;
use Greg\Support\Obj;

class HttpKernel
{
    const EVENT_RUN = 'http.run';

    const EVENT_DISPATCHING = 'http.dispatching';

    const EVENT_DISPATCHED = 'http.dispatched';

    const EVENT_FINISHED = 'http.finished';

    private $app = null;

    private $router = null;

    private $controllersPrefixes = [];

    private $loadedControllers = [];

    public function __construct(Application $app = null, Router $router = null)
    {
        $this->app = $app ?: new Application();

        $this->router = $router ?: new Router();

        $this->addDispatcherToRouter();

        $this->bootServiceProviders();

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

    public function bootstrap(ServiceProvider $serviceProvider)
    {
        $this->app()->bootstrap($serviceProvider);

        $serviceProvider->bootHttpKernel($this);

        return $this;
    }

    public function addControllersPrefixes(string $prefix, string ...$prefixes)
    {
        $this->controllersPrefixes[] = (string) $prefix;

        $this->controllersPrefixes = array_merge($this->controllersPrefixes, $prefixes);

        $this->fixControllersPrefixes();

        return $this;
    }

    public function setControllersPrefixes(string $prefix, string ...$prefixes)
    {
        $this->controllersPrefixes = $prefixes;

        array_unshift($this->controllersPrefixes, $prefix);

        $this->fixControllersPrefixes();

        return $this;
    }

    public function getControllersPrefixes()
    {
        return $this->controllersPrefixes;
    }

    public function run(?string $path = null, ?string $method = null): Response
    {
        return $this->app->run(function () use ($path, $method) {
            $path = $path ?: Request::relativeUriPath();

            $method = $method ?: Request::method();

            $this->app->fire(static::EVENT_RUN, $path);

            if ($route = $this->router->detect($path, $method, $data)) {
                $this->app->fire(static::EVENT_DISPATCHING, $path, $route, $data);

                $response = $route->exec($data);

                if (!($response instanceof Response)) {
                    $response = new Response($response);
                }

                $this->app->fire(static::EVENT_DISPATCHED, $path, $route, $data, $response);
            } else {
                $response = new Response('Route not found.', null, 404);
            }

            $this->app->fire(static::EVENT_FINISHED, $path, $response);

            return $response;
        });
    }

    private function bootServiceProviders()
    {
        foreach ($this->app()->serviceProviders() as $serviceProvider) {
            $serviceProvider->bootHttpKernel($this);
        }

        return $this;
    }

    protected function boot()
    {
    }

    private function controllerExists($name)
    {
        return Obj::exists($name, array_merge($this->controllersPrefixes, ['']));
    }

    private function addDispatcherToRouter()
    {
        $this->router->setDispatcher(function ($action) {
            if (!is_scalar($action)) {
                return $action;
            }

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

            return function (...$params) use ($action) {
                return $this->app->ioc()->call($action, ...$params);
            };
        });

        return $this;
    }

    private function getController($name)
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

    private function fixControllersPrefixes()
    {
        $this->controllersPrefixes = array_unique($this->controllersPrefixes);

        return $this;
    }
}
