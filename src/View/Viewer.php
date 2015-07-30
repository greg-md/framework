<?php

namespace Greg\View;

use Greg\Support\Engine\InternalTrait;
use Greg\Http\Response;
use Greg\Support\Storage\AccessorTrait;
use Greg\Support\Storage\ArrayAccessTrait;
use Greg\Support\Arr;
use Greg\Support\Obj;
use Greg\Support\Str;

class Viewer implements \ArrayAccess
{
    use AccessorTrait, ArrayAccessTrait, InternalTrait;

    protected $extension = '.php';

    protected $paths = [];

    protected $layouts = [];

    protected $content = null;

    protected $parent = null;

    /**
     * @var CompilerInterface[]|callable[]
     */
    protected $compilers = [];

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
            }

            $content = $this->fetchLayouts($content, ...$layout);
        }

        return $content;
    }

    public function fetchName($name, $responseObject = true)
    {
        return $this->fetchFileName($this->toFileName($name), $responseObject);
    }

    public function fetchFileName($fileName, $responseObject = true)
    {
        if ($file = $this->getFile($fileName)) {
            return $this->fetchFile($file, $responseObject);
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

    public function renderName($name, array $params = [], $responseObject = true)
    {
        return $this->renderFileName($this->toFileName($name), $params, $responseObject);
    }

    public function renderFileName($fileName, array $params = [], $responseObject = true)
    {
        if ($file = $this->getFile($fileName)) {
            return $this->renderFile($file, $params, $responseObject);
        }

        throw new \Exception('View file `' . $fileName . '` does not exist in view paths.');
    }

    public function renderFile($file, array $params = [], $responseObject = true)
    {
        $viewer = clone $this;

        $viewer->assign($params);

        $viewer->parent($this);

        return $viewer->fetchFile($file, $responseObject);
    }

    public function partial($name, array $params = [])
    {
        return $this->partialName($name, $params);
    }

    public function partialName($name, array $params = [])
    {
        return $this->partialFileName($this->toFileName($name), $params);
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

        $compiler = $this->getCompilerByFile($file);

        if ($compiler) {
            $data = $compiler->fetchFile($file);
        } else {
            ob_start();

            try {
                $this->includeFile($file);

                $data = ob_get_clean();
            } catch (\Exception $e) {
                ob_end_clean();

                throw $e;
            }
        }

        return $responseObject ? Response::create($this->appName(), $data) : $data;
    }

    public function getCompilerByFile($file)
    {
        foreach($this->compilers as $extension => $compiler) {
            if (Str::endsWith($file, $extension)) {
                return $this->getCompiler($extension);
            }
        }

        return false;
    }

    public function getCompiler($extension)
    {
        if (!Arr::has($this->compilers, $extension)) {
            throw new \Exception('View compiler for extension `' . $extension . '` not found.');
        }

        $compiler = &$this->compilers[$extension];

        if (is_callable($compiler)) {
            $compiler = $this->app()->binder()->call($compiler);
        }

        if (!($compiler instanceof CompilerInterface)) {
            throw new \Exception('View compiler for extension `' . $extension . '` should be an instance of `CompilerInterface`.');
        }

        return $compiler;
    }

    public function setCompiler($extension, $compiler)
    {
        $this->compilers[$extension] = $compiler;

        return $this;
    }

    public function fetchLayouts($content, $layout, $_ = null)
    {
        return $this->fetchLayoutsAs(true, ...func_get_args());
    }

    public function fetchLayoutsAs($responseObject, $content, $layout, $_ = null)
    {
        if (!is_array($layout)) {
            $layout = func_get_args();

            // Remote $responseObject
            array_shift($layout);

            // Remote $content
            array_shift($layout);
        }

        $this->content($content);

        foreach($layouts = $layout as $layout) {
            $content = $this->fetchName($layout, $responseObject);

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

    public function layouts($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
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