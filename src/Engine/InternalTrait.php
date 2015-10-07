<?php

namespace Greg\Engine;

use Greg\Application\Runner;
use Greg\Support\Tool\Debug;
use Greg\Support\Tool\Obj;

trait InternalTrait
{
    protected $appName = 'greg';

    /**
     * @param $appName
     * @param ...$args
     * @return static
     * @throws \Exception
     */
    static public function newInstance($appName = 'greg', ...$args)
    {
        return static::newInstanceRef($appName, ...$args);
    }

    /**
     * @param string $appName
     * @param ...$args
     * @return static
     * @throws \Exception
     */
    static protected function newInstanceRef($appName = 'greg', &...$args)
    {
        /* @var $app \Greg\Application\Runner */
        $app = Memory::get($appName . '@app');

        if (!$app) {
            throw new \Exception('App `' . $appName . '` is not registered in memory.');
        }

        return $app->loadInstanceArgs(get_called_class(), $args);
    }

    /**
     * @param string $appName
     * @return static
     * @throws \Exception
     */
    static public function instance($appName = 'greg')
    {
        /* @var $app \Greg\Application\Runner */
        $app = Memory::get($appName . '@app');

        if (!$app) {
            throw new \Exception('App `' . $appName . '` is not registered in memory.');
        }

        return $app->getInstance(get_called_class());
    }

    public function memory($key = null, $value = null)
    {
        return $this->memoryRef(...func_get_args());
    }

    public function &memoryRef($key = null, &$value = null)
    {
        $memoryKey = $this->appName();

        if ($num = func_num_args()) {
            $memoryKey .= '@' . $key;

            if ($num > 1) {
                Memory::setRef($memoryKey, $value);

                return $this;
            }
        }

        return Memory::getRef($memoryKey);
    }

    /**
     * @param Runner $runner
     * @return Runner|null
     */
    public function app(Runner $runner = null)
    {
        return $this->memory('app', ...func_get_args());
    }

    public function appName($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    protected function callCallable(callable $callable, ...$args)
    {
        return call_user_func_array($callable, $this->app()->binder()->getCallableArgs($callable, $args));
    }

    protected function callCallableWith(callable $callable, ...$args)
    {
        return call_user_func_array($callable, $this->app()->binder()->getCallableMixedArgs($callable, $args));
    }

    protected function loadClassInstance($className, ...$args)
    {
        return $this->app()->loadInstanceArgs($className, $args);
    }

    public function scopeCallable(callable $callable)
    {
        return $this->app()->binder()->call($callable);
    }

    public function _()
    {
        return $this->app()->helper();
    }

    public function __debugInfo()
    {
        return Debug::fixInfo($this, get_object_vars($this));
    }
}