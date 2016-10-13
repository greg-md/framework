<?php

namespace Greg\View;

use Greg\Support\Accessor\ArrayAccessTrait;
use Greg\Support\Http\Response;
use Greg\Support\Obj;
use Greg\Support\Str;
use Greg\View\Compiler\BladeCompiler;
use Greg\View\Compiler\CompilerInterface;

class Viewer implements \ArrayAccess
{
    use ArrayAccessTrait;

    protected $paths = [];

    protected $layouts = [];

    protected $content = null;

    /**
     * @var Viewer|null
     */
    protected $parent = null;

    /**
     * @var CompilerInterface[]|callable[]
     */
    protected $compilers = [];

    public function __construct($paths = [], array $data = [])
    {
        $this->setPaths((array) $paths);

        $this->setAccessor($data);

        return $this;
    }

    public function fetchLayout($name)
    {
        return $this->fetch($name, ...$this->layouts);
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
        if ($file = $this->getFile($name)) {
            return $this->fetchFile($file, $responseObject);
        }

        throw new \Exception('View file `' . $name . '` does not exist in view paths.');
    }

    public function fetchNameIfExists($name, $responseObject = true)
    {
        if ($file = $this->getFile($name)) {
            return $this->fetchFile($file, $responseObject);
        }

        return null;
    }

    public function renderLayout($name, array $params = [])
    {
        return $this->render($name, $params, ...$this->layouts);
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
        if ($file = $this->getFile($name)) {
            return $this->renderFile($file, $params, $responseObject);
        }

        throw new \Exception('View file `' . $name . '` does not exist in view paths.');
    }

    public function renderFile($file, array $params = [], $responseObject = true)
    {
        $viewer = clone $this;

        $viewer->assign($params);

        $viewer->setParent($this);

        return $viewer->fetchFile($file, $responseObject);
    }

    public function partial($name, array $params = [], $responseObject = true)
    {
        return $this->partialName($name, $params, $responseObject);
    }

    public function partialName($name, array $params = [], $responseObject = true)
    {
        if ($file = $this->getFile($name)) {
            return $this->partialFile($file, $params, $responseObject);
        }

        throw new \Exception('View file `' . $name . '` does not exist in view paths.');
    }

    public function partialFile($file, array $params = [], $responseObject = true)
    {
        $viewer = clone $this;

        $viewer->assign($params, true);

        $viewer->setParent($this);

        return $viewer->fetchFile($file, $responseObject);
    }

    public function partialLoop($name, array $items, array $params = [])
    {
        return $this->partialNameLoop($name, $items, $params);
    }

    public function partialNameLoop($name, array $items, array $params = [])
    {
        if ($file = $this->getFile($name)) {
            return $this->partialFileLoop($file, $items, $params);
        }

        throw new \Exception('View file `' . $name . '` does not exist in view paths.');
    }

    public function partialFileLoop($file, array $items, array $params = [])
    {
        $content = [];

        foreach ($items as $key => $item) {
            $viewer = clone $this;

            $viewer->assign(array_merge(['item' => $item, 'key' => $key], $params), true);

            $viewer->setParent($this);

            $content[] = $viewer->fetchFile($file, false);
        }

        return $this->newResponse(implode('', $content));
    }

    protected function newResponse($content)
    {
        return new Response($content);
    }

    public function fetchFile($file, $responseObject = true)
    {
        $compiler = $this->findCompilerByFile($file);

        $content = $this->loadFile($compiler ? $compiler->getCompiledFile($file) : $file);

        return $responseObject ? $this->newResponse($content) : $content;
    }

    public function loadFile($___file)
    {
        if (!file_exists($___file)) {
            throw new \Exception('View file not found.');
        }

        ob_start();

        try {
            include $___file;

            $data = ob_get_clean();
        } catch (\Exception $e) {
            ob_end_clean();

            throw $e;
        }

        return $data;
    }

    /**
     * @param $file
     *
     * @throws \Exception
     *
     * @return bool|CompilerInterface|BladeCompiler
     */
    public function findCompilerByFile($file)
    {
        $compilers = $this->compilers;

        uksort($compilers, function ($a, $b) {
            return gmp_cmp($a, $b) * -1;
        });

        foreach ($compilers as $extension => $compiler) {
            if (Str::endsWith($file, $extension)) {
                return $this->getCompiler($extension);
            }
        }

        return false;
    }

    public function getCompilerByFile($file)
    {
        if (!$compiler = $this->findCompilerByFile($file)) {
            throw new \Exception('Compiler was not found for this file.');
        }

        return $compiler;
    }

    public function getCompiler($extension)
    {
        if (!array_key_exists($extension, $this->compilers)) {
            throw new \Exception('View compiler for extension `' . $extension . '` not found.');
        }

        $compiler = &$this->compilers[$extension];

        if (is_callable($compiler)) {
            $compiler = Obj::callCallable($compiler);
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

        $this->setContent($content);

        foreach ($layouts = $layout as $layout) {
            $content = $this->fetchName($layout, $responseObject);

            $this->setContent($content);
        }

        $this->setContent(null);

        return $content;
    }

    public function getFile($name)
    {
        if (!$paths = $this->getPaths()) {
            throw new \Exception('Undefined view paths.');
        }

        $extensions = $this->getExtensions();

        foreach ($paths as $path) {
            if (is_dir($path)) {
                foreach ($extensions as $extension) {
                    $file = $path . DIRECTORY_SEPARATOR . ltrim($name . $extension, '\/');

                    if (is_file($file)) {
                        return $file;
                    }
                }
            }
        }

        return false;
    }

    public function getExtensions()
    {
        $extensions = $this->getCompilersExtensions();

        if (!in_array('.php', $extensions)) {
            $extensions[] = '.php';
        }

        return $extensions;
    }

    public function getCompilersExtensions()
    {
        return array_keys($this->compilers);
    }

    public function assign($key, $value = null)
    {
        $this->setToAccessor($key, $value);

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

    public function setPaths(array $paths)
    {
        $this->paths = $paths;

        return $this;
    }

    public function getPaths()
    {
        return $this->paths;
    }

    public function setContent($content)
    {
        $this->content = (string) $content;

        return $this;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setParent(Viewer $viewer)
    {
        $this->parent = $viewer;

        return $this;
    }

    public function getParent()
    {
        return $this->parent;
    }
}
