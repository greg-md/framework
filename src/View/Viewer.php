<?php

namespace Greg\View;

use Greg\Engine\Internal;
use Greg\Http\Request;
use Greg\Storage\ArrayAccess;
use Greg\Support\Obj;
use Greg\Support\Str;

class Viewer
{
    use ArrayAccess, Internal;

    protected $request = null;

    protected $controllers = [];

    protected $extension = '.phtml';

    protected $paths = [];

    public function __construct(Request $request, $paths = null, $param = [])
    {
        $this->request($request);

        $this->paths($paths);

        $this->replace($param);

        return $this;
    }

    public function renderView($name, $controllerName = null)
    {
        if ($controllerName) {
            $controller = $this->controllers($controllerName);

            if (!$controller) {
                $controller = $this->app()->loadController($controllerName, $this->request(), $this);
            }
        } else {
            $controller = current($this->controllers());

            if (!$controller) {
                throw new Exception('No render view controller defined.');
            }

            $controllerName = $controller->name();
        }

        if ($controller) {
            $viewName = Str::phpName($name) . 'View';

            if (method_exists($controller, $viewName)) {
                $controller->$viewName();
            }
        }

        return $this->renderName($controllerName . '/' . $name);
    }

    public function renderName($name, $include = true)
    {
        return $this->render($this->nameToFile($name), $include);
    }

    public function render($file, $include = true)
    {
        $paths = $this->paths();

        if (!$paths) {
            throw new Exception('Undefined view paths.');
        }

        $data = null;

        foreach ($paths as $path) {
            $data = $this->renderPath($path, $file, $include);
            if ($data !== false) {
                break;
            }
        }

        if ($data === false) {
            throw new Exception('View file `' . $file . '` does not exist in view paths.');
        }

        return $data;
    }

    public function renderPath($path, $file, $include = true)
    {
        if (!is_dir($path)) {
            return false;
        }

        $fullPath = $path . DIRECTORY_SEPARATOR . ltrim($file, '\/');

        if (!file_exists($fullPath)) {
            return false;
        }

        if ($include) {
            ob_start();

            try {
                include $fullPath;

                $data = ob_get_clean();
            } catch (Exception $e) {
                ob_get_clean();

                throw $e;
            }
        } else {
            $data = file_get_contents($fullPath);
        }

        return (string)$data;
    }

    public function nameToFile($name)
    {
        return $name . $this->extension();
    }

    public function assign($key, $value = null)
    {
        if (is_array($key)) {
            $this->replace($key);
        } else {
            $this->set($key, $value);
        }

        return $this;
    }

    public function __set($key, $value)
    {
        return $this->set($key, $value);
    }

    public function __get($key)
    {
        return $this->get($key);
    }

    public function request(Request $value = null)
    {
        return Obj::fetchVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function controllers($key = null, $value = null, $type = Obj::VAR_APPEND, $recursive = false, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function extension($value = null, $type = Obj::VAR_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function paths($key = null, $value = null, $type = Obj::VAR_APPEND, $recursive = false, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function __call($method, array $args = [])
    {
        $app = $this->app();

        if ($app->hasComponent($method)) {
            return $app->getComponent($method);
        }

        throw new Exception('Component `' . $method . '` not found!');
    }
}