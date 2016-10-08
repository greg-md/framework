<?php

namespace Greg;

use Greg\Event\Listener;
use Greg\Event\SubscriberInterface;
use Greg\Loader\ClassLoader;
use Greg\Router\Router;
use Greg\Support\Accessor\ArrayAccessTrait;
use Greg\Support\Arr;
use Greg\Support\Http\Request;
use Greg\Support\Http\Response;
use Greg\Support\IoC\IoCContainer;
use Greg\Support\IoC\IoCManager;
use Greg\Support\Server;
use Greg\Support\Session;
use Greg\Support\Str;
use Greg\Translation\Translator;
use Greg\View\Viewer;

class Application implements \ArrayAccess
{
    use ArrayAccessTrait;

    protected $appName = 'greg';

    const EVENT_INIT = 'app.init';

    const EVENT_RUN = 'app.run';

    const EVENT_DISPATCHING = 'app.dispatching';

    const EVENT_DISPATCHED = 'app.dispatched';

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
        $this->loadComponents();

        $this->getListener()->fire(static::EVENT_INIT);

        return $this;
    }

    protected function loadComponents()
    {
        foreach($this->getIndexArray('app.components') as $component) {
            $this->addComponent($component);
        }

        return $this;
    }

    public function addComponent($component)
    {
        if (!is_object($component)) {
            $component = $this->loadInstance($component);
        }

        $this->setToMemory('component:' . get_class($component), $component);

        $this->getIoCContainer()->setObject($component);

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

            if (Str::isScalar($response)) {
                $response = new Response($response);
            }

            $this->getListener()->fireWith(static::EVENT_DISPATCHED, $path, $route, $response);
        } else {
            $response = new Response();
        }

        return $response;
    }

    /**
     * @param string $path
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

    public function getIoCContainer()
    {
        if (!$class = $this->getFromMemory('ioc_container')) {
            $this->setToMemory('ioc_container', $class = new IoCContainer());

            $class->setObject($class);

            $class->setObject($this);
        }

        return $class;
    }

    public function getIoCManager()
    {
        if (!$class = $this->getFromMemory('ioc_manager')) {
            $class = (new IoCManager())->setIoCContainer($this->getIoCContainer());

            $this->setToMemory('ioc_manager', $class);

            $this->getIoCContainer()->setObject($class);
        }

        return $class;
    }

    public function getLoader()
    {
        if (!$class = $this->getFromMemory('loader')) {
            $this->setToMemory('loader', $class = new ClassLoader());

            $class->register(true);

            $this->getIoCContainer()->setObject($class);
        }

        return $class;
    }

    public function getListener()
    {
        if (!$class = $this->getFromMemory('listener')) {
            $this->setToMemory('listener', $class = new Listener($this->getIoCManager()));

            $this->getIoCContainer()->setObject($class);
        }

        return $class;
    }

    public function getSession()
    {
        if (!$class = $this->getFromMemory('session')) {
            $this->setToMemory('session', $class = new Session());

            $this->getIoCContainer()->setObject($class);
        }

        return $class;
    }

    public function getTranslator()
    {
        if (!$class = $this->getFromMemory('translator')) {
            $this->setToMemory('translator', $class = new Translator());

            $this->getIoCContainer()->setObject($class);
        }

        return $class;
    }

    public function getViewer()
    {
        if (!$class = $this->getFromMemory('viewer')) {
            $this->setToMemory('viewer', $class = new Viewer());

            $this->getIoCContainer()->setObject($class);
        }

        return $class;
    }

    public function getRouter()
    {
        if (!$class = $this->getFromMemory('router')) {
            $this->setToMemory('router', $class = new Router($this->getIoCManager()));

            $this->getIoCContainer()->setObject($class);
        }

        return $class;
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

    public function loadInstance($className, ...$args)
    {
        return $this->loadInstanceArgs($className, $args);
    }

    public function loadInstanceArgs($className, array $args = [])
    {
        $class = $this->getIoCContainer()->loadInstanceArgs($className, $args);

        // Disable autorun init methods for a while
        //$this->initClassInstance($class);

        return $class;
    }

    protected function initClassInstance($class)
    {
        $ioc = $this->getIoCContainer();

        if (method_exists($class, 'init')) {
            $ioc->call([$class, 'init']);
        }

        // Call all methods which starts with init
        foreach(get_class_methods($class) as $methodName) {
            if ($methodName[0] === 'i' and $methodName !== 'init' and Str::startsWith($methodName, 'init')) {
                $ioc->call([$class, $methodName]);
            }
        }

        return $this;
    }

    public function getInstance($className)
    {
        $instance = $this->getFromMemory('instances:' . $className);

        if (!$instance) {
            $instance = $this->loadInstance($className);

            $this->setToMemory('instances:' . $className, $instance);
        }

        return $instance;
    }
}
