<?php

namespace Greg\Framework;

class Application implements \ArrayAccess
{
    const EVENT_RUN = 'app.run';

    const EVENT_FINISHED = 'app.finished';

    private $config = [];

    private $ioc = null;

    private $bootstraps = [];

    private $events = [];

    public function __construct(Config $config = null, IoCContainer $ioc = null)
    {
        $this->config = $config ?: new Config();

        $this->ioc = $ioc ?: new IoCContainer();

        $this->ioc->register($this);

        $this->boot();

        return $this;
    }

    public function config(): Config
    {
        return $this->config;
    }

    public function ioc(): IoCContainer
    {
        return $this->ioc;
    }

    public function bootstrap(BootstrapStrategy $class)
    {
        $this->bootstraps[get_class($class)] = $class;

        $class->boot($this);

        return $this;
    }

    public function getBootstrap(string $name)
    {
        return $this->bootstraps[$name] ?? null;
    }

    public function getBootstraps()
    {
        return $this->bootstraps;
    }

    public function listen($eventNames, $listener)
    {
        $this->validateListener($listener);

        foreach ((array) $eventNames as $eventName) {
            $this->events[$eventName][] = $listener;
        }

        return $this;
    }

    public function fire(string $eventName, &...$arguments)
    {
        return $this->fireArgs($eventName, $arguments);
    }

    public function fireArgs(string $eventName, array $arguments)
    {
        foreach ($this->events[$eventName] ?? [] as $listener) {
            $this->handleListener($listener, $arguments);
        }

        return $this;
    }

    public function event($event)
    {
        if (!is_object($event)) {
            throw new \Exception('Event is not an object.');
        }

        return $this->fire(get_class($event), $event);
    }

    public function scope(callable $callable)
    {
        return $this->ioc->call($callable);
    }

    public function run(callable $callable)
    {
        $this->fire(static::EVENT_RUN);

        $response = $this->ioc->call($callable);

        $this->fire(static::EVENT_FINISHED);

        return $response;
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
        return $this->config->removeIndex($key);
    }

    protected function boot()
    {
    }

    private function validateListener($listener)
    {
        if (!is_callable($listener) and !is_object($listener) and !class_exists($listener, false)) {
            throw new \Exception('Unknown listener type');
        }

        return $this;
    }

    private function handleListener($listener, array $arguments)
    {
        if (is_callable($listener)) {
            $this->ioc->callArgs($listener, $arguments);

            return $this;
        }

        if (!is_object($listener)) {
            $listener = $this->ioc->load($listener);
        }

        if (!method_exists($listener, 'handle')) {
            throw new \Exception('Listener `' . get_class($listener) . '` does not have `handle` method.');
        }

        $this->ioc->callArgs([$listener, 'handle'], $arguments);

        return $this;
    }
}
