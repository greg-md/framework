<?php

namespace Greg\Engine;

use Greg\Support\Obj;

trait Internal
{
    protected $appName = 'default';

    /**
     * @param string $appName
     * @return static
     * @throws \Exception
     */
    static public function create($appName = 'default')
    {
        $app = Memory::get($appName . ':app');

        if (!$app) {
            throw new \Exception('App `' . $appName . '` is not registered in memory.');
        }

        $args = func_get_args();
        //array_shift($args);
        //array_unshift($args, get_called_class());
        $args[0] = get_called_class(); // better solution instead of shifting

        return call_user_func_array([$app, 'newClass'], $args);
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

        $args = func_get_args();

        if ($args) {
            $key .= ':' . array_shift($args);

            if ($args) {
                Memory::set($key, array_shift($args));

                return $this;
            }
        }

        return Memory::get($key);
    }

    /**
     * @return \Greg\Application\Runner
     */
    public function app()
    {
        return $this->memory('app');
    }

    public function appName($value = null, $type = Obj::VAR_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, func_get_args());
    }
}