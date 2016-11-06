<?php

namespace Greg\Http;

use Greg\Application;
use Greg\Router\Router;
use Greg\Support\Http\Request;
use Greg\Support\Http\Response;
use Greg\Support\Obj;
use Greg\Support\Str;

class HttpKernel
{
    const EVENT_INIT = 'http.init';

    const EVENT_RUN = 'http.run';

    const EVENT_DISPATCHING = 'http.dispatching';

    const EVENT_DISPATCHED = 'http.dispatched';

    const EVENT_FINISHED = 'http.finished';

    protected $app = null;

    protected $controllersPrefixes = [];

    public function __construct(Application $app)
    {
        $this->app = $app;

        $this->init();

        return $this;
    }

    protected function init()
    {
        $this->setControllersPrefixes($this->app->config()->getIndexArray('http.controllers_prefixes'));

        $this->app->getIoCContainer()->inject(Router::class, function () {
            return $this->getRouter();
        });

        $this->loadComponents();

        $this->app->getListener()->fire(static::EVENT_INIT);

        return $this;
    }

    protected function loadComponents()
    {
        foreach ($this->app->config()->getIndexArray('http.components') as $key => $component) {
            $this->app->addComponent($component, $key);
        }

        return $this;
    }

    public function setControllersPrefixes(array $prefixes)
    {
        $this->controllersPrefixes = $prefixes;

        return $this;
    }

    public function getControllersPrefixes()
    {
        return $this->controllersPrefixes;
    }

    public function appendControllersPrefixes(array $prefixes)
    {
        $this->controllersPrefixes = array_merge($this->controllersPrefixes, $prefixes);

        return $this;
    }

    public function prependControllersPrefixes(array $prefixes)
    {
        $this->controllersPrefixes = array_merge($prefixes, $this->controllersPrefixes);

        return $this;
    }

    public function run($path = '/')
    {
        if (!func_num_args()) {
            $path = Request::relativeUriPath();
        } else {
            $path = $path ?: '/';
        }

        $listener = $this->app->getListener();

        $listener->fireWith(static::EVENT_RUN, $path);

        if ($route = $this->getRouter()->findRouteByPath($path)) {
            $listener->fireWith(static::EVENT_DISPATCHING, $path, $route);

            $response = $route->dispatch();

            if (!($response instanceof Response)) {
                $response = new Response($response);
            }

            $listener->fireWith(static::EVENT_DISPATCHED, $path, $route, $response);
        } else {
            $response = new Response();
        }

        $listener->fireWith(static::EVENT_FINISHED, $path, $response);

        return $response;
    }

    /**
     * @param string $path
     *
     * @return Response
     */
    public function dispatch($path)
    {
        $response = $this->getRouter()->dispatchPath($path);

        if (Str::isScalar($response)) {
            $response = new Response($response);
        }

        return $response;
    }

    public function getRouter()
    {
        if (!$class = $this->app->getFromMemory('router')) {
            $this->app->setToMemory('router', $class = new Router());

            $class->setCallCallableWith([$this->app->getIoCContainer(), 'callWith']);

            $this->addDispatcherToRouter($class);
        }

        return $class;
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

            return $action;
        });

        return $this;
    }

    protected function getController($name)
    {
        if (!$className = $this->controllerExists($name)) {
            throw new \Exception('Controller `' . $name . '` not found.');
        }

        if (!$class = $this->app->getFromMemory('controller:' . $className)) {
            $class = $this->app->load($className);

            $this->app->setToMemory('controller:' . $className, $class);

            $this->app->initClassInstance($class);
        }

        return $class;
    }

    public function controllerExists($name)
    {
        return Obj::classExists($name, array_merge($this->getControllersPrefixes(), ['']));
    }
}
