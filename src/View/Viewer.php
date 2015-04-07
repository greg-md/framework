<?php

namespace Greg\View;

use Greg\Engine\Internal;
use Greg\Http\Request;
use Greg\Storage\Accessor;
use Greg\Storage\ArrayAccess;
use Greg\Support\Arr;
use Greg\Support\Obj;
use Greg\Support\Str;

class Viewer implements \ArrayAccess
{
    use Accessor, ArrayAccess, Internal;

    protected $extension = '.phtml';

    protected $paths = [];

    public function __construct($paths = [], array $param = [])
    {
        Arr::bringRef($paths);

        $this->paths($paths);

        $this->storage($param);

        return $this;
    }

    static public function create($appName, $paths = [], array $param = [])
    {
        return static::newInstanceRef($appName, $paths, $param);
    }

    public function renderName($name, $include = true, $throwException = true)
    {
        return $this->render($this->nameToFile($name), $include, $throwException);
    }

    public function render($fileName, $include = true, $throwException = true)
    {
        $paths = $this->paths();

        if (!$paths) {
            throw Exception::newInstance($this->appName(), 'Undefined view paths.');
        }

        $data = false;

        foreach ($paths as $path) {
            if (!is_dir($path)) {
                continue;
            }

            $file = $path . DIRECTORY_SEPARATOR . ltrim($fileName, '\/');

            if (!is_file($file)) {
                continue;
            }

            $data = $this->renderFile($file, $include);

            break;
        }

        if ($throwException and $data === false) {
            throw Exception::newInstance($this->appName(), 'View file `' . $fileName . '` does not exist in view paths.');
        }

        return $data;
    }

    public function renderFile($file, $include = true)
    {
        if (!is_file($file)) {
            throw Exception::newInstance($this->appName(), 'You can render only from files.');
        }

        if ($include) {
            ob_start();

            try {
                $this->includeFile($file);

                $data = ob_get_clean();
            } catch (Exception $e) {
                ob_get_clean();

                throw $e;
            }
        } else {
            $data = file_get_contents($file);
        }

        return (string)$data;
    }

    public function includeFile($file)
    {
        include $file;

        return $this;
    }

    public function nameToFile($name)
    {
        return $name . $this->extension();
    }

    public function assign($key, $value = null)
    {
        return $this->storage(...func_num_args());
    }

    public function __set($key, $value)
    {
        return $this->set($key, $value);
    }

    public function __get($key)
    {
        return $this->get($key);
    }

    public function extension($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function paths($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function __call($method, array $args = [])
    {
        $components = $this->app()->components();

        if ($components->has($method)) {
            return $components->get($method);
        }

        throw Exception::newInstance($this->appName(), 'Component `' . $method . '` not found!');
    }
}