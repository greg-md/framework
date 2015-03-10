<?php

namespace Greg\Engine;

use Greg\Application\Runner;
use Greg\Support\Obj;

trait Internal
{
    protected $appName = 'default';

    /**
     * @param string $appName
     * @param ...$args
     * @return static
     * @throws \Exception
     */
    static public function create($appName = 'default', ...$args)
    {
        /* @var $app \Greg\Application\Runner */
        $app = Memory::get($appName . ':app');

        if (!$app) {
            throw new \Exception('App `' . $appName . '` is not registered in memory.');
        }

        return $app->newInstance(get_called_class(), ...$args);
    }

    /**
     * @param string $appName
     * @return static
     * @throws \Exception
     */
    static public function instance($appName = 'default')
    {
        /* @var $app \Greg\Application\Runner */
        $app = Memory::get($appName . ':app');

        if (!$app) {
            throw new \Exception('App `' . $appName . '` is not registered in memory.');
        }

        return $app->getInstance(get_called_class());
    }

    public function &memory($key = null, $value = null)
    {
        $key = $this->appName();

        if ($args = func_get_args()) {
            $key .= ':' . array_shift($args);

            if ($args) {
                Memory::set($key, array_shift($args));

                return $this;
            }
        }

        return Memory::get($key);
    }

    /**
     * @param Runner $app
     * @return Runner|null
     */
    public function app(Runner $app = null)
    {
        return $this->memory('app', ...func_get_args());
    }

    public function appName($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, func_get_args());
    }
}