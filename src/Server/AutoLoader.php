<?php

namespace Greg\Server;

use Greg\Engine\Internal;
use Greg\Support\Arr;
use Greg\Support\Obj;

class AutoLoader
{
    use Internal;

    protected $paths = [];

    protected $throwException = true;

    protected $prepend = false;

    public function __construct($paths = null, $throwException = null, $prepend = null)
    {
        if ($paths !== null) {
            $this->paths(Arr::bring($paths));
        }

        if ($throwException !== null) {
            $this->throwException($throwException);
        }

        if ($prepend !== null) {
            $this->prepend($prepend);
        }

        $this->run();

        return $this;
    }

    public function load($name)
    {
        $file = static::nameToPath($name) . '.php';

        $file = static::resolvePath($file, $this->paths());

        if ($file !== false) {
            require_once $file;

            static::shouldExists($name);
        }

        return $this;
    }

    public function run()
    {
        return spl_autoload_register([$this, 'load'], $this->throwException(), $this->prepend());
    }

    public function stop()
    {
        return spl_autoload_unregister([$this, 'load']);
    }

    public function paths($key = null, $value = null, $type = Obj::VAR_APPEND, $recursive = false, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function throwException($value = null)
    {
        return Obj::fetchBoolVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function prepend($value = null)
    {
        return Obj::fetchBoolVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    static public function registerIncludedPaths($throw = true, $prepend = false)
    {
        return spl_autoload_register(get_called_class() . '::loadFromIncludedPaths', $throw, $prepend);
    }

    static public function unRegisterIncludedPaths()
    {
        return spl_autoload_unregister(get_called_class() . '::loadFromIncludedPaths');
    }

    static public function loadFromIncludedPaths($name)
    {
        $file = static::nameToPath($name) . '.php';

        $file = stream_resolve_include_path($file);

        if ($file !== false) {
            require_once $file;

            AutoLoader::shouldExists($name);

            return true;
        }

        return false;
    }

    static public function nameToPath($name)
    {
        return strtr(ltrim($name, '\\'), [
            '_' => DIRECTORY_SEPARATOR,
            '\\' => DIRECTORY_SEPARATOR,
        ]);
    }

    static public function resolvePath($file, $paths = [])
    {
        foreach ($paths as $path) {
            $fullPath = $path . DIRECTORY_SEPARATOR . $file;

            if (is_file($fullPath)) return $fullPath;
        }

        return false;
    }

    static public function registerPaths(&$paths = [], $throwException = true, $prepend = false)
    {
        return spl_autoload_register($function = function($name) use ($paths) {
            $file = AutoLoader::nameToPath($name) . '.php';

            $file = AutoLoader::resolvePath($file, $paths);

            if ($file !== false) {
                require_once $file;

                AutoLoader::shouldExists($name);

                return true;
            }

            return false;
        }, $throwException, $prepend) ? $function : false;
    }

    static public function shouldExists($name)
    {
        if (!class_exists($name, false) and !interface_exists($name, false) and !trait_exists($name, false)) {
            throw new \Exception('`' . $name . '` not found.');
        }

        return true;
    }
}