<?php

namespace Greg\Application;

use Greg\Composer\Autoload\ClassLoader;
use Greg\Engine\Internal;
use Greg\Engine\InternalInterface;
use Greg\Event\Listener;
use Greg\Event\SubscriberInterface;
use Greg\Http\Request;
use Greg\Http\Response;
use Greg\Router\Dispatcher;
use Greg\Server\Config;
use Greg\Server\Ini;
use Greg\Server\Session;
use Greg\Storage\ArrayAccess;
use Greg\Support\Obj;
use Greg\Support\Str;
use Greg\Translation\Translator;
use Greg\View\Viewer;
use Closure;

class Runner implements \ArrayAccess, InternalInterface
{
    use ArrayAccess, Internal;

    const EVENT_INIT = 'app.init';
    const EVENT_RUN = 'app.run';
    const EVENT_STARTUP = 'app.startup';
    const EVENT_DISPATCH = 'app.dispatch';
    const EVENT_FINISH = 'app.finish';

    protected $viewPaths = [];

    protected $controllersPrefixes = [];

    protected $modelsPrefixes = [];

    public function __construct($settings = [], $appName = null)
    {
        if ($appName) {
            $this->appName($appName);
        }

        $this->memory('app', $this);

        $this->replace($settings);

        return $this;
    }

    public function init()
    {
        $this->initConfig();

        $this->initServer();

        $this->initBinder();

        $this->initLoader();

        $this->initListener();

        $this->initResponse();

        $this->initTranslator();

        $this->initResources();

        $this->initRouter();

        $this->initSubscribers();

        $this->initComponents();

        // Fire app init event
        $this->listener()->fire(static::EVENT_INIT);

        return $this;
    }

    public function initConfig()
    {
        $this->viewPaths((array)$this->indexGet('view.paths'));

        $this->controllersPrefixes((array)$this->indexGet('controllers.prefixes'));

        return $this;
    }

    public function initServer()
    {
        // Server ini
        if ($serverIni = (array)$this->indexGet('server.ini')) {
            Ini::param($serverIni);
        }

        // Server config
        if ($serverConfig = (array)$this->indexGet('server.config')) {
            Config::param($serverConfig);
        }

        // Session ini
        if ($sessionIni = (array)$this->indexGet('session.ini')) {
            Session::ini($sessionIni);
        }

        // Session persistent
        if ($this->indexHas('session.persistent')) {
            Session::persistent((bool)$this->indexGet('session.persistent'));
        }

        return $this;
    }

    public function initBinder()
    {
        // Load Binder
        $this->binder($binder = Binder::create($this->appName()));

        // Add Binder to Binder
        $binder->add($binder);

        // Add myself to Binder
        $binder->add($this);

        // Register instances prefixes
        $binder->instancesPrefixes((array)$this->indexGet('binder.instances_prefixes'));

        return $this;
    }

    public function initLoader()
    {
        // Load ClassLoader
        $this->loader($loader = ClassLoader::create($this->appName(), $this->indexGet('loader.paths')));

        // Register the ClassLoader to autoload
        $loader->register(true);

        // Add ClassLoader to Binder
        $this->binder()->add($loader);

        return $this;
    }

    public function initListener()
    {
        // Load Listener
        $this->listener($listener = Listener::create($this->appName()));

        // Add Listener to Binder
        $this->binder()->add($listener);

        return $this;
    }

    public function initResponse()
    {
        // Load Response
        $this->response($response = Response::create($this->appName()));

        // Add Response to Binder
        $this->binder()->add($response);

        return $this;
    }

    public function initTranslator()
    {
        // Load Translator
        $this->translator($translator = Translator::create($this->appName()));

        // Add Translator to Binder
        $this->binder()->add($translator);

        return $this;
    }

    public function initResources()
    {
        // Load Resources Manager
        $this->resources($resources = Resources::create($this->appName()));

        // Add Resources Manager to Binder
        $this->binder()->add($resources);

        // Add Resources to Manager
        $resources->addMore((array)$this['resources']);

        // Register Resources Manager to Binder adapters
        $this->binder()->addAdapter('resources', [$resources, 'get'], array_flip($resources->getClasses()));

        return $this;
    }

    public function initRouter()
    {
        // Load Router
        $this->router($router = Dispatcher::create($this->appName()));

        // Add Router to Binder
        $this->binder()->add($router);

        return $this;
    }

    public function initSubscribers()
    {
        // Load subscribers
        if ($subscribers = (array)$this->get('subscribers')) {
            $this->listener()->addSubscribers($subscribers);
        }

        return $this;
    }

    public function initComponents()
    {
        // Load components
        if ($components = (array)$this->get('components')) {
            $this->addComponents($components);
        }

        return $this;
    }

    public function run($path = '/')
    {
        if (!func_num_args() and $requestRouteParam = $this->indexGet('request.route_param')) {
            $path = Request::getRequest($requestRouteParam);
        }

        $path = $path ?: '/';

        $this->listener()->fireArgs(static::EVENT_RUN, [&$path]);

        $this->router()->fetch($path);

        $this->listener()->fire(static::EVENT_STARTUP);

        $content = $this->router()->dispatch();

        $this->response()->body($content);

        $this->listener()->fire(static::EVENT_DISPATCH);

        $this->response()->send();

        $this->listener()->fire(static::EVENT_FINISH);

        return $this;
    }

    public function addComponents(array $components, Closure $callback = null)
    {
        foreach ($components as $name => $component) {
            $this->addComponent($name, $component, $callback);
        }

        return $this;
    }

    public function addComponent($name, $component, Closure $callback = null)
    {
        if (is_string($component)) {
            $component = $this->newClass($component);
        }

        $this->memory('component/' . $name, $component);

        $this->binder()->add($component);

        if ($component instanceof SubscriberInterface) {
            $this->listener()->subscribe('component/' . $name, $component);
        }

        if ($callback) {
            $callback($component);
        }

        return $this;
    }

    public function hasComponent($name)
    {
        return $this->memory('component/' . $name) ? true : false;
    }

    public function getComponent($name)
    {
        $component = $this->memory('component/' . $name);

        if (!$component) {
            throw Exception::create($this->appName(), 'Undefined component `' . $name . '`.');
        }

        return $component;
    }

    public function newClass($className)
    {
        $args = func_get_args();

        array_shift($args);

        if (is_bool($className)) {
            $loadExtended = $className;
            $className = array_shift($args);
        } else {
            $loadExtended = true;
        }

        $className = $loadExtended ? $this->getExtended($className) : $className;

        $binder = $this->binder();

        /* @var $class InternalInterface */
        $class = $binder ? $binder->newClass($className, $args) : Obj::instanceArgs($className, $args);

        $class->appName($this->appName());

        if (method_exists($class, 'init')) {
            $binder ? $binder->call([$class, 'init']) : $class->init();
        }

        return $class;
    }

    public function getInstance($className)
    {
        $args = func_get_args();

        array_shift($args);

        if (is_bool($className)) {
            $loadExtended = $className;
            $className = array_shift($args);
        } else {
            $loadExtended = true;
        }

        $className = $loadExtended ? $this->getExtended($className) : $className;

        $instance = $this->memory('instances/' . $className);
        if (!$instance) {
            $binder = $this->binder();

            /* @var $instance InternalInterface */
            $instance = $binder ? $binder->newClass($className) : Obj::instanceArgs($className);

            $instance->appName($this->appName());

            if (method_exists($instance, 'init')) {
                $binder ? $binder->call([$instance, 'init']) : $instance->init();
            }

            $this->memory('instances/' . $className, $instance);
        }

        return $instance;
    }

    public function getExtended($class)
    {
        $extended = (array)$this->get('extended');

        return array_key_exists($class, $extended) ? $extended[$class] : $class;
    }

    public function action($name = null, $controllerName = null, $param = [])
    {
        $name = $name ?: 'index';

        $controllerName = $controllerName ?: 'index';

        $request = Request::create($this->appName(), $param);

        $view = $this->viewCreate($request, $param);

        $controller = $this->loadController($controllerName, $request, $view);

        if ($controller) {
            $actionName = Str::phpName($name) . 'Action';
            if (method_exists($controller, $actionName)) {
                $controller->$actionName();
            }
        }

        return $view->renderName($controllerName . '/' . $name . '.action');
    }

    /**
     * @param $request
     * @param array $param
     * @return Viewer
     * @throws \Greg\Engine\Exception
     */
    public function viewCreate($request, array $param = [])
    {
        return Viewer::create($this->appName(), $request, $this->viewPaths()->toArray(), $param);
    }

    public function loadController($name, $request, $view)
    {
        $name = Str::phpName($name);

        $prefixes = array_merge([''], $this->controllersPrefixes()->toArray());
        //$prefixes = $this->controllersPrefixes()->toArray();

        foreach($prefixes as $prefix) {
            /* @var $class Controller */
            $class = $prefix . '\Controllers\\' . $name . '\Controller';
            if (class_exists($class)) {
                return $class::create($this->appName(), $name, $request, $view);
            }
        }

        return false;
    }

    public function loadModel($name)
    {
        $prefixes = array_merge([''], $this->modelsPrefixes()->toArray());

        foreach($prefixes as $prefix) {
            /* @var $class Controller */
            $class = $prefix . '\Model\\' . $name;
            if (class_exists($class)) {
                return $class::create($this->appName());
            }
        }

        throw Exception::create('Model `' . $name . '` not found!');
    }

    /**
     * @param ClassLoader $loader
     * @return ClassLoader|bool
     */
    public function loader(ClassLoader $loader = null)
    {
        return func_num_args() ? $this->memory('loader', $loader) : $this->memory('loader');
    }

    /**
     * @param Binder $binder
     * @return Binder|bool
     */
    public function binder(Binder $binder = null)
    {
        return func_num_args() ? $this->memory('binder', $binder) : $this->memory('binder');
    }

    /**
     * @param Listener $listener
     * @return Listener|bool
     */
    public function listener(Listener $listener = null)
    {
        return func_num_args() ? $this->memory('listener', $listener) : $this->memory('listener');
    }

    /**
     * @param Response $response
     * @return Response|bool
     */
    public function response(Response $response = null)
    {
        return func_num_args() ? $this->memory('response', $response) : $this->memory('response');
    }

    /**
     * @param Translator $translator
     * @return Translator|bool
     */
    public function translator(Translator $translator = null)
    {
        return func_num_args() ? $this->memory('translator', $translator) : $this->memory('translator');
    }

    /**
     * @param Resources $resources
     * @return Resources|bool
     */
    public function resources(Resources $resources = null)
    {
        return func_num_args() ? $this->memory('resources', $resources) : $this->memory('resources');
    }

    /**
     * @param Dispatcher $router
     * @return Dispatcher|bool
     */
    public function router(Dispatcher $router = null)
    {
        return func_num_args() ? $this->memory('router', $router) : $this->memory('router');
    }

    public function viewPaths($key = null, $value = null, $type = Obj::VAR_APPEND, $replace = false)
    {
        return Obj::fetchArrayObjVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function controllersPrefixes($key = null, $value = null, $type = Obj::VAR_APPEND, $replace = false)
    {
        return Obj::fetchArrayObjVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function modelsPrefixes($key = null, $value = null, $type = Obj::VAR_APPEND, $replace = false)
    {
        return Obj::fetchArrayObjVar($this, $this->{__FUNCTION__}, func_get_args());
    }
}
