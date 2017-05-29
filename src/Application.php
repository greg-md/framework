<?php

namespace Greg\Framework;

use Greg\Support\Server;
use Greg\Support\Str;

class Application implements ApplicationContract
{
    private $config = [];

    private $ioc = null;

    private $components = [];

    private $events = [];

    public function __construct(Config $config = null, IoCContainer $ioc = null, string $abstract = null)
    {
        if (!$config) {
            $config = new Config();
        }

        if (!$ioc) {
            $ioc = new IoCContainer();
        }

        if (!$abstract) {
            $abstract = ApplicationContract::class;
        }

        $this->config = $config;

        $this->ioc = $ioc;

        $this->ioc->concrete($abstract, $this);

        $this->boot();

        return $this;
    }

    protected function boot()
    {
    }

    public function addComponent($component, $name = null)
    {
        if (!is_object($component)) {
            $component = $this->ioc->load($component);
        }

        $this->components[$name ?: get_class($component)] = $component;

        if (method_exists($component, 'init')) {
            $this->ioc->call([$component, 'init']);
        }

        // Call all methods which starts with "init"
        foreach (get_class_methods($component) as $methodName) {
            if ($methodName[0] === 'i' and $methodName !== 'init' and Str::startsWith($methodName, 'init')) {
                $this->ioc->call([$component, $methodName]);
            }
        }

        return $this;
    }

    public function basePath()
    {
        return $this['base_path'] ?: realpath(Server::documentRoot() . '/..');
    }

    public function publicPath()
    {
        return $this['public_path'] ?: Server::documentRoot();
    }

    public function debugMode()
    {
        return (bool) $this['debug_mode'];
    }

    public function run(callable $callable)
    {
        $this->fireWith(static::EVENT_RUN);

        $response = $this->scope($callable);

        $this->fireWith(static::EVENT_FINISHED);

        return $response;
    }

    public function on($event, $listener)
    {
        foreach ((array) $event as $e) {
            $this->events[$e][] = $listener;
        }

        return $this;
    }

    public function fire($event, ...$args)
    {
        return $this->fireArgs($event, ...$args);
    }

    public function fireRef($event, &...$args)
    {
        return $this->fireArgs($event, $args);
    }

    protected function fireArgs($event, array $args = [])
    {
        if (array_key_exists($event, $this->events)) {
            foreach ((array) $this->events[$event] as $listener) {
                if (is_callable($listener)) {
                    $this->ioc->call($listener, ...$args);
                } else {
                    if (!is_object($listener)) {
                        $listener = $this->ioc->load($listener);
                    }

                    $method = Str::phpLowerCamelCase($event);

                    if (!method_exists($listener, $method)) {
                        throw new \Exception('Undefined method `' . $method . '` in listener `' . get_class($listener) . '`.');
                    }

                    $this->ioc->call([$listener, $method], ...$args);
                }
            }
        }

        return $this;
    }

    public function fireWith($event, ...$args)
    {
        return $this->fireWithRef($event, ...$args);
    }

    public function fireWithRef($event, &...$args)
    {
        return $this->fireWithArgs($event, $args);
    }

    protected function fireWithArgs($event, array $args = [])
    {
        if (array_key_exists($event, $this->events)) {
            foreach ((array) $this->events[$event] as &$listener) {
                if (is_callable($listener)) {
                    $this->ioc->callWith($listener, ...$args);
                } else {
                    if (!is_object($listener)) {
                        $listener = $this->ioc->load($listener);
                    }

                    $method = Str::phpLowerCamelCase($event);

                    if (!method_exists($listener, $method)) {
                        throw new \Exception('Undefined method `' . $method . '` in listener `' . get_class($listener) . '`.');
                    }

                    $this->ioc->callWith([$listener, $method], ...$args);
                }
            }
        }

        return $this;
    }

    public function scope(callable $callable)
    {
        return $this->ioc->call($callable);
    }

    public function config()
    {
        return $this->config;
    }

    public function ioc()
    {
        return $this->ioc;
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
}
