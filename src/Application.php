<?php

namespace Greg;

use Greg\Event\Listener;
use Greg\Event\SubscriberInterface;
use Greg\Router\Router;
use Greg\Support\Accessor\ArrayAccessTrait;
use Greg\Support\Arr;
use Greg\Support\Http\Request;
use Greg\Support\Http\Response;
use Greg\Support\IoC\IoCContainer;
use Greg\Support\Obj;
use Greg\Support\Server;
use Greg\Support\Str;

class Application implements \ArrayAccess
{
    use ArrayAccessTrait;

    protected $appName = 'greg';

    protected $controllersPrefixes = [];

    const EVENT_INIT = 'app.init';

    const EVENT_RUN = 'app.run';

    const EVENT_DISPATCHING = 'app.dispatching';

    const EVENT_DISPATCHED = 'app.dispatched';

    const EVENT_FINISHED = 'app.finished';

    public function __construct(array $settings = [], $appName = null)
    {
        if ($appName !== null) {
            $this->setAppName($appName);
        }

        $this->setToMemory('app', $this);

        $this->setAccessor(Arr::fixIndexes($settings));

        return $this;
    }

    public function init()
    {
        $this->setControllersPrefixes($this->getIndexArray('app.controllers_prefixes'));

        $this->loadComponents();

        $this->getListener()->fire(static::EVENT_INIT);

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

    protected function loadComponents()
    {
        foreach ($this->getIndexArray('app.components') as $key => $component) {
            $this->addComponent($component, $key);
        }

        return $this;
    }

    public function addComponent($component, $name = null)
    {
        if (!is_object($component)) {
            $component = $this->getIoCContainer()->loadInstance($component);
        }

        $this->setToMemory('component:' . $name ?: get_class($component), $component);

        $this->initClassInstance($component);

        if ($component instanceof SubscriberInterface) {
            $component->subscribe($this->getListener());
        }

        return $this;
    }

    public function run($path = '/')
    {
        if (!func_num_args()) {
            $path = Request::relativeUriPath();
        } else {
            $path = $path ?: '/';
        }

        $this->getListener()->fireWith(static::EVENT_RUN, $path);

        if ($route = $this->getRouter()->findRouteByPath($path)) {
            $this->getListener()->fireWith(static::EVENT_DISPATCHING, $path, $route);

            $response = $route->dispatch();

            if (!($response instanceof Response)) {
                $response = new Response($response);
            }

            $this->getListener()->fireWith(static::EVENT_DISPATCHED, $path, $route, $response);
        } else {
            $response = new Response();
        }

        $this->getListener()->fireWith(static::EVENT_FINISHED, $path);

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

    public function basePath()
    {
        return $this->getIndex('app.base_path') ?: realpath(Server::documentRoot() . '/..');
    }

    public function publicPath()
    {
        return $this->getIndex('app.public_path') ?: Server::documentRoot();
    }

    public function debugMode()
    {
        return (bool) $this->getIndex('app.debug_mode');
    }

    public function getIoCContainer()
    {
        if (!$class = $this->getFromMemory('ioc_container')) {
            $this->setToMemory('ioc_container', $class = new IoCContainer());

            $class->setObject($class);

            $class->setObject($this);

            $class->set(Listener::class, function () {
                return $this->getListener();
            });

            $class->set(Router::class, function () {
                return $this->getRouter();
            });

            $class->addPrefixes($this->getIndexArray('app.injectable_prefixes'));

            $class->setMore($this->getIndexArray('app.injectable'));
        }

        return $class;
    }

    public function getListener()
    {
        if (!$class = $this->getFromMemory('listener')) {
            $this->setToMemory('listener', $class = new Listener());

            $class->setCallCallable([$this->getIoCContainer(), 'call']);

            $class->setCallCallableWith([$this->getIoCContainer(), 'callWith']);

            $this->getIoCContainer()->setObjectForce($class);
        }

        return $class;
    }

    public function getRouter()
    {
        if (!$class = $this->getFromMemory('router')) {
            $this->setToMemory('router', $class = new Router());

            $class->setCallCallableWith([$this->getIoCContainer(), 'callWith']);

            $this->addDispatcherToRouter($class);

            $this->getIoCContainer()->setObjectForce($class);
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

        if (!$class = $this->getFromMemory('controller:' . $className)) {
            $class = $this->getIoCContainer()->loadInstance($className);

            $this->setToMemory('controller:' . $className, $class);

            $this->initClassInstance($class);
        }

        return $class;
    }

    public function controllerExists($name)
    {
        return Obj::classExists($name, array_merge($this->getControllersPrefixes(), ['']));
    }

    public function setToMemory($key, $value)
    {
        Memory::setValueRef($this->getAppName() . '@' . $key, $value);

        return $this;
    }

    public function getFromMemory($key)
    {
        return Memory::getRef($this->getAppName() . '@' . $key);
    }

    public function setAppName($name)
    {
        $this->appName = (string) $name;

        return $this;
    }

    public function getAppName()
    {
        return $this->appName;
    }

    protected function initClassInstance($class)
    {
        $ioc = $this->getIoCContainer();

        if (method_exists($class, 'init')) {
            $ioc->call([$class, 'init']);
        }

        // Call all methods which starts with "init"
        foreach (get_class_methods($class) as $methodName) {
            if ($methodName[0] === 'i' and $methodName !== 'init' and Str::startsWith($methodName, 'init')) {
                $ioc->call([$class, $methodName]);
            }
        }

        return $this;
    }

    public function inject($name, $object = null)
    {
        $this->getIoCContainer()->set($name, $object);

        return $this;
    }

    public function scope(callable $callable)
    {
        return $this->getIoCContainer()->call($callable);
    }

    public function once($name, callable $callable = null)
    {
        throw new \Exception('Application->once() is under construction.');
    }
}
