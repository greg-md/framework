<?php

namespace Greg;

use Greg\Event\Listener;
use Greg\Event\SubscriberInterface;
use Greg\Support\IoC\IoCContainer;
use Greg\Support\Server;
use Greg\Support\Str;

class Application implements \ArrayAccess
{
    const EVENT_INIT = 'app.init';

    const EVENT_RUN = 'app.run';

    const EVENT_FINISHED = 'app.finished';

    protected $appName = 'greg';

    protected $config = null;

    public function __construct(array $settings = [], $appName = null)
    {
        if ($appName !== null) {
            $this->setAppName($appName);
        }

        $this->setToMemory('app', $this);

        $this->config = new Config($settings);

        $this->init();

        return $this;
    }

    protected function init()
    {
        $this->loadComponents();

        $this->getListener()->fire(static::EVENT_INIT);

        return $this;
    }

    protected function loadComponents()
    {
        foreach ($this->config()->getIndexArray('app.components') as $key => $component) {
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

    public function run(callable $callable)
    {
        $this->getListener()->fireWith(static::EVENT_RUN);

        $this->scope($callable);

        $this->getListener()->fireWith(static::EVENT_FINISHED);

        return $this;
    }

    public function basePath()
    {
        return $this['app.base_path'] ?: realpath(Server::documentRoot() . '/..');
    }

    public function publicPath()
    {
        return $this['app.public_path'] ?: Server::documentRoot();
    }

    public function debugMode()
    {
        return (bool) $this['app.debug_mode'];
    }

    /**
     * @return IoCContainer
     * @throws \Exception
     */
    public function getIoCContainer()
    {
        if (!$class = $this->getFromMemory('ioc_container')) {
            $this->setToMemory('ioc_container', $class = new IoCContainer());

            $class->object($class);

            $class->object($this);

            $class->inject(Listener::class, function () {
                return $this->getListener();
            });

            $class->addPrefixes($this->config->getIndexArray('app.injectable_prefixes'));

            foreach($this->config->getIndexArray('app.injectable') as $abstract => $loader) {
                if (is_int($abstract)) {
                    $class->inject($loader);
                } else {
                    $class->inject($abstract, $loader);
                }
            }
        }

        return $class;
    }

    public function getListener()
    {
        if (!$class = $this->getFromMemory('listener')) {
            $this->setToMemory('listener', $class = new Listener());

            $class->setCallCallable([$this->getIoCContainer(), 'call']);

            $class->setCallCallableWith([$this->getIoCContainer(), 'callWith']);
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

    public function hasInMemory($key)
    {
        return Memory::has($this->getAppName() . '@' . $key);
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

    public function initClassInstance($class)
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

    public function inject($abstract, $loader = null)
    {
        $this->getIoCContainer()->inject($abstract, $loader);

        return $this;
    }

    public function concrete($abstract, $concrete)
    {
        $this->getIoCContainer()->concrete($abstract, $concrete);

        return $this;
    }

    public function object($concrete)
    {
        $this->getIoCContainer()->object($concrete);

        return $this;
    }

    public function expect($name)
    {
        return $this->getIoCContainer()->expect($name);
    }

    public function load($name, ...$args)
    {
        return $this->getIoCContainer()->loadInstanceArgs($name, $args);
    }

    public function scope(callable $callable)
    {
        return $this->getIoCContainer()->call($callable);
    }

    public function config()
    {
        return $this->config;
    }

    public function offsetExists($key)
    {
        return $this->config->hasIndex($key);
    }

    public function offsetSet($key, $value)
    {
        return $this->config->setIndex($key, $value);
    }

    public function offsetGet($key)
    {
        return $this->config->getIndex($key);
    }

    public function offsetUnset($key)
    {
        return $this->config->delIndex($key);
    }
}
