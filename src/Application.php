<?php

namespace Greg\Framework;

use Greg\DependencyInjection\IoCContainer;
use Greg\Support\Arr;

class Application implements \ArrayAccess
{
    const EVENT_RUN = 'app.run';

    const EVENT_FINISHED = 'app.finished';

    private $config = [];

    private $ioc;

    private $events = [];

    private $serviceProviders = [];

    public function __construct(array $config = [], IoCContainer $ioc = null)
    {
        $this->config = Arr::fixIndexes($config);

        $this->ioc = $ioc ?: new IoCContainer();

        $this->boot();

        return $this;
    }

    public function config(string $index = null)
    {
        return $index ? Arr::getIndex($this->config, $index) : $this->config;
    }

    public function addConfig(string $name, array $config)
    {
        $this->config[$name] = Arr::fixIndexes($config);

        return $this;
    }

    public function removeConfig(string $name)
    {
        unset($this->config[$name]);

        return $this;
    }

    public function ioc(): IoCContainer
    {
        return $this->ioc;
    }

    public function addServiceProvider(ServiceProvider $serviceProvider)
    {
        $this->serviceProviders[$serviceProvider->name()] = $serviceProvider;

        $this->callServiceProvider($serviceProvider, 'boot', $this);

        return $this;
    }

    public function callServiceProvider(ServiceProvider $serviceProvider, string $method, ...$arguments)
    {
        if (method_exists($serviceProvider, $method)) {
            $this->ioc->call([$serviceProvider, $method], ...$arguments);
        }

        return $this;
    }

    public function getServiceProvider(string $name): ?ServiceProvider
    {
        return $this->serviceProviders[$name] ?? null;
    }

    /**
     * @return ServiceProvider[]
     */
    public function getServiceProviders(): array
    {
        return $this->serviceProviders;
    }

    public function listen($eventNames, $listener)
    {
        $this->validateListener($listener);

        foreach ((array) $eventNames as $eventName) {
            $this->events[$eventName][] = $listener;
        }

        return $this;
    }

    public function fire(string $eventName, ...$arguments)
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

    public function inject(string $abstract, $concrete, ...$arguments)
    {
        return $this->ioc->inject($abstract, $concrete, ...$arguments);
    }

    public function register($object)
    {
        return $this->ioc->register($object);
    }

    public function get($abstract)
    {
        return $this->ioc->get($abstract);
    }

    public function expect($abstract)
    {
        return $this->ioc->expect($abstract);
    }

    public function scope(callable $callable)
    {
        return $this->ioc->call($callable);
    }

    public function run(callable $callable)
    {
        $this->fire(static::EVENT_RUN);

        $response = $this->ioc->call($callable, $this);

        $this->fire(static::EVENT_FINISHED);

        return $response;
    }

    public function offsetExists($key)
    {
        return Arr::hasIndex($this->config, $key);
    }

    public function offsetSet($key, $value)
    {
        return Arr::setIndex($this->config, $key, Arr::fixIndexes($value));
    }

    public function offsetGet($key)
    {
        return Arr::getIndex($this->config, $key);
    }

    public function offsetUnset($key)
    {
        return Arr::removeIndex($this->config, $key);
    }

    protected function boot()
    {
    }

    private function validateListener($listener)
    {
        if (!is_callable($listener) and !is_object($listener) and !class_exists($listener)) {
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
