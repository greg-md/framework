<?php

namespace Greg\Http;

use Greg\ApplicationContract;
use Greg\Router\Route;
use Greg\Router\Router;
use Greg\Support\Http\Request;
use Greg\Support\Http\Response;
use Greg\Support\Obj;

class HttpKernel implements HttpKernelContract
{
    private $app = null;

    private $router = null;

    protected $controllersPrefixes = [];

    private $loadedControllers = [];

    public function __construct(ApplicationContract $app)
    {
        $this->app = $app;

        $this->router = new Router();

        $this->addDispatcherToRouter($this->router);

        $this->boot();

        return $this;
    }

    protected function boot()
    {
    }

    public function run($path = '/')
    {
        if (!func_num_args()) {
            $path = Request::relativeUriPath();
        } else {
            $path = $path ?: '/';
        }

        $this->app->fireWith(static::EVENT_RUN, $path);

        if ($route = $this->router->findRouteByPath($path)) {
            $this->app->fireWith(static::EVENT_DISPATCHING, $path, $route);

            $response = $route->dispatch();

            if (!($response instanceof Response)) {
                $response = new Response($response);
            }

            $this->app->fireWith(static::EVENT_DISPATCHED, $path, $route, $response);
        } else {
            try {
                $response = $this->router->dispatchException(new \Exception('Route not found.'));

                if (!($response instanceof Response)) {
                    $response = new Response($response);
                }
            } catch (\Exception $e) {
                $response = (new Response($e->getMessage()))->setCode(404);
            }
        }

        $this->app->fireWith(static::EVENT_FINISHED, $path, $response);

        return $response;
    }

    protected function addDispatcherToRouter(Router $router)
    {
        $router->setDispatcher(function ($action) {
            $parts = explode('@', $action, 2);

            if (!isset($parts[1])) {
                throw new \Exception('Action name is not defined in router controller `' . $parts[0] . '`.');
            }

            list($controllerName, $actionName) = $parts;

            $action = [$this->getController($controllerName), $actionName];

            return function (Route $route, Request $request, ...$params) use ($action) {
                return $this->app->ioc()->callWith($action, $route, $request, ...$params);
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
        return Obj::classExists($name, array_merge($this->controllersPrefixes, ['']));
    }

    protected function fixControllersPrefixes()
    {
        $this->controllersPrefixes = array_unique($this->controllersPrefixes);

        return $this;
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

    public function app()
    {
        return $this->app;
    }

    public function router()
    {
        return $this->router;
    }
}
