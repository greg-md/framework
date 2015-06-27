<?php

namespace Greg\View;

use Greg\Support\Engine\InternalTrait;
use Greg\Http\Response;
use Greg\Support\Storage\AccessorTrait;
use Greg\Support\Storage\ArrayAccessTrait;
use Greg\Support\Arr;
use Greg\Support\Obj;

class Viewer implements \ArrayAccess
{
    use AccessorTrait, ArrayAccessTrait, InternalTrait;

    protected $extension = '.php';

    protected $paths = [];

    protected $layouts = [];

    protected $content = null;

    protected $parent = null;

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

    public function fetchLayout($name)
    {
        return $this->fetch($name, ...$this->layouts());
    }

    public function fetchIfExists($name, $layout = null, $_ = null)
    {
        if ($this->fileExists($this->toFileName($name))) {
            return $this->fetch(...func_get_args());
        }

        return null;
    }

    public function fetch($name, $layout = null, $_ = null)
    {
        $content = $this->fetchName($name);

        if ($layout) {
            if (!is_array($layout)) {
                $layout = func_get_args();

                array_shift($layout);

                array_shift($layout);
            }

            $content = $this->fetchLayouts($content, ...$layout);
        }

        return $content;
    }

    public function fetchName($name)
    {
        return $this->fetchFileName($this->toFileName($name));
    }

    public function fetchFileName($fileName)
    {
        if ($file = $this->getFile($fileName)) {
            return $this->fetchFile($file);
        }

        throw new \Exception('View file `' . $fileName . '` does not exist in view paths.');
    }

    public function renderLayout($name, array $params = [])
    {
        return $this->render($name, $params, ...$this->layouts());
    }

    public function render($name, array $params = [], $layout = null, $_ = null)
    {
        $content = $this->renderName($name, $params);

        if ($layout) {
            if (!is_array($layout)) {
                $layout = func_get_args();

                array_shift($layout);

                array_shift($layout);
            }

            $content = $this->fetchLayouts($content, ...$layout);
        }

        return $content;
    }

    public function renderName($name, array $params = [])
    {
        return $this->renderFileName($this->toFileName($name), $params);
    }

    public function renderFileName($fileName, array $params = [])
    {
        if ($file = $this->getFile($fileName)) {
            return $this->renderFile($file, $params);
        }

        throw new \Exception('View file `' . $fileName . '` does not exist in view paths.');
    }

    public function renderFile($file, array $params = [])
    {
        $viewer = clone $this;

        $viewer->assign($params);

        $viewer->parent($this);

        return $viewer->fetchFile($file);
    }

    public function partial($name, array $params = [])
    {
        return $this->partialName($name, $params);
    }

    public function partialName($name, array $params = [])
    {
        return $this->renderFileName($this->toFileName($name), $params);
    }

    public function partialFileName($fileName, array $params = [])
    {
        if ($file = $this->getFile($fileName)) {
            return $this->partialFile($file, $params);
        }

        throw new \Exception('View file `' . $fileName . '` does not exist in view paths.');
    }

    public function partialFile($file, array $params = [])
    {
        $viewer = clone $this;

        $viewer->assign($params, true);

        $viewer->parent($this);

        return $viewer->fetchFile($file);
    }

    public function partialLoop($name, array $items, array $params = [])
    {
        return $this->partialNameLoop($name, $items, $params);
    }

    public function partialNameLoop($name, array $items, array $params = [])
    {
        return $this->partialFileNameLoop($this->toFileName($name), $items, $params);
    }

    public function partialFileNameLoop($fileName, array $items, array $params = [])
    {
        if ($file = $this->getFile($fileName)) {
            return $this->partialFileLoop($file, $items, $params);
        }

        throw new \Exception('View file `' . $fileName . '` does not exist in view paths.');
    }

    public function partialFileLoop($file, array $items, array $params = [])
    {
        $result = [];

        foreach($items as $item) {
            $viewer = clone $this;

            $viewer->assign(array_merge(['item' => $item], $params), true);

            $viewer->parent($this);

            $result[] = $viewer->fetchFile($file, false);
        }

        return Response::create($this->appName(), implode('', $result));
    }

    public function fetchFile($file, $responseObject = true)
    {
        if (!is_file($file)) {
            throw new \Exception('You can render only files.');
        }

        ob_start();

        try {
            $this->includeFile($file);

            $data = ob_get_clean();
        } catch (\Exception $e) {
            ob_end_clean();

            throw $e;
        }

        return $responseObject ? Response::create($this->appName(), $data) : $data;
    }

    public function fetchLayouts($content, $layout, $_ = null)
    {
        if (!is_array($layout)) {
            $layout = func_get_args();

            array_shift($layout);
        }

        $this->content($content);

        foreach($layouts = $layout as $layout) {
            $content = $this->fetchName($layout);

            $this->content($content);
        }

        $this->content(null);

        return $content;
    }

    public function includeFile($file)
    {
        include $file;

        return $this;
    }

    public function fileExists($fileName)
    {
        return $this->getFile($fileName) !== false;
    }

    public function getFile($fileName)
    {
        $paths = $this->paths();

        if (!$paths) {
            throw new \Exception('Undefined view paths.');
        }

        foreach ($paths as $path) {
            if (is_dir($path)) {
                $file = $path . DIRECTORY_SEPARATOR . ltrim($fileName, '\/');

                if (is_file($file)) {
                    return $file;
                }
            }
        }

        return false;
    }

    public function toFileName($name)
    {
        return $name . $this->extension();
    }

    public function assign($key, $value = null)
    {
        return $this->storage(...func_get_args());
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

    public function layouts($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function content($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    /**
     * @param Viewer $value
     * @return $this|Viewer
     */
    public function parent(Viewer $value = null)
    {
        return Obj::fetchVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function __call($method, array $args = [])
    {
        $components = $this->app()->components();

        if ($components->has($method)) {
            return $components->get($method);
        }

        throw new \Exception('Component `' . $method . '` not found.');
    }
}