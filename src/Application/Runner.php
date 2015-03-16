<?php

namespace Greg\Application;

use Greg\Composer\Autoload\ClassLoader;
use Greg\Engine\Internal;
use Greg\Event\Listener;
use Greg\Http\Request;
use Greg\Http\Response;
use Greg\Router\Dispatcher;
use Greg\Server\Config;
use Greg\Server\Ini;
use Greg\Server\Session;
use Greg\Storage\Accessor;
use Greg\Storage\ArrayAccess;
use Greg\Support\Arr;
use Greg\Support\Obj;
use Greg\Support\Str;
use Greg\Translation\Translator;
use Greg\View\Viewer;

class Runner implements \ArrayAccess
{
    use Accessor, ArrayAccess, Internal;

    const EVENT_INIT = 'app.init';

    const EVENT_RUN = 'app.run';

    const EVENT_STARTUP = 'app.startup';

    const EVENT_DISPATCH = 'app.dispatch';

    const EVENT_FINISH = 'app.finish';

    protected $viewPaths = [];

    protected $controllersPrefixes = [];

    protected $modelsPrefixes = [];

    protected $extended = [];

    public function __construct(array $settings = [], $appName = null)
    {
        if ($appName !== null) {
            $this->appName($appName);
        }

        $this->memory('app', $this);

        $this->storage($settings);

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
        $this->viewPaths($this->getIndexArray('view.paths'));

        $this->controllersPrefixes($this->getIndexArray('controllers.prefixes'));

        $this->extended($this->getArray('extended'));

        return $this;
    }

    public function initServer()
    {
        // Server ini
        if ($serverIni = $this->getIndexArray('server.ini')) {
            Ini::param($serverIni);
        }

        // Server config
        if ($serverConfig = $this->getIndexArray('server.config')) {
            Config::param($serverConfig);
        }

        // Session ini
        if ($sessionIni = $this->getIndexArray('session.ini')) {
            Session::ini($sessionIni);
        }

        // Session persistent
        if ($this->hasIndex('session.persistent')) {
            Session::persistent((bool)$this->getIndex('session.persistent'));
        }

        return $this;
    }

    public function initBinder()
    {
        // Load Binder
        $this->binder($binder = Binder::create($this->appName()), $this->getIndexArray('binder'));

        // Add Binder to Binder
        $binder->setObject($binder);

        // Add myself to Binder
        $binder->setObject($this);

        // Register instances prefixes
        $binder->instancesPrefixes($this->getIndexArray('binder.instances_prefixes'));

        return $this;
    }

    public function initLoader()
    {
        // Load ClassLoader
        $this->loader($loader = ClassLoader::create($this->appName(), $this->getIndexArray('loader.paths')));

        // Register the ClassLoader to autoload
        $loader->register(true);

        // Add ClassLoader to Binder
        $this->binder()->setObject($loader);

        return $this;
    }

    public function initListener()
    {
        // Load Listener
        $this->listener($listener = Listener::create($this->appName(), $this->getIndexArray('listener')));

        // Add Listener to Binder
        $this->binder()->setObject($listener);

        return $this;
    }

    public function initResponse()
    {
        // Load Response
        $this->response($response = Response::create($this->appName()));

        // Add Response to Binder
        $this->binder()->setObject($response);

        return $this;
    }

    public function initTranslator()
    {
        // Load Translator
        $this->translator($translator = Translator::create($this->appName(),
                            $this->getIndexArray('translator.languages'),
                            $this->getIndexArray('translator.translates')));

        // Add Translator to Binder
        $this->binder()->setObject($translator);

        return $this;
    }

    public function initResources()
    {
        // Load Resources Manager
        $this->resources($resources = Resources::create($this->appName(), $this->getIndexArray('resources')));

        // Add Resources Manager to Binder
        $this->binder()->setObject($resources);

        // Register Resources Manager to Binder adapters
        $this->binder()->addAdapter('resources', [$resources, 'get'], array_flip($resources->getClasses()));

        return $this;
    }

    public function initRouter()
    {
        // Load Subscribers
        $this->router($router = Dispatcher::newInstance($this->appName(), $this->getArray('router.routes')));

        // Add Subscribers to Binder
        $this->binder()->setObject($router);

        return $this;
    }

    public function initSubscribers()
    {
        // Load subscribers
        if ($subscribers = $this->getArray('subscribers')) {
            $this->listener()->addSubscribers($subscribers);
        }

        return $this;
    }

    public function initComponents()
    {
        // Load Components Manager
        $this->components($components = Components::create($this->appName(), $this->getArray('components')));

        // Add Components Manager to Binder
        $this->binder()->setObject($components);

        return $this;
    }

    public function loadInstance($className, ...$args)
    {
        return $this->loadInstanceArgs($className, $args);
    }

    public function loadInstanceArgs($className, array $args = [])
    {
        if (is_bool($className)) {
            $loadExtended = $className;

            $className = array_shift($args);
        } else {
            $loadExtended = true;
        }

        $className = $loadExtended ? $this->getExtended($className) : $className;

        $binder = $this->binder();

        /* @var $class Internal */
        $class = $binder ? $binder->loadInstanceArgs($className, $args) : Obj::loadInstanceArgs($className, $args);

        $class->appName($this->appName());

        if (method_exists($class, 'init')) {
            $binder ? $binder->call([$class, 'init']) : $class->init();
        }

        return $class;
    }

    public function getInstance($className, ...$args)
    {
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

            /* @var $instance Internal */
            $instance = $binder ? $binder->loadInstance($className) : Obj::loadInstance($className);

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
        return Arr::get($this->extended, $class, $class);
    }

    public function run($path = '/')
    {
        if (!func_num_args() and $requestRouteParam = $this->getIndex('request.route_param')) {
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

    public function action($name = null, $controllerName = null, $param = [])
    {
        $name = $name ?: 'index';

        $controllerName = $controllerName ?: 'index';

        $request = Request::create($this->appName(), $param);

        $view = $this->newView($request, $param);

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
     * @throws \Exception
     */
    public function newView(Request $request, array $param = [])
    {
        return Viewer::create($this->appName(), $request, $this->viewPaths(), $param);
    }

    public function loadController($name, Request $request, Viewer $view)
    {
        $name = Str::phpName($name);

        $prefixes = array_merge([''], $this->controllersPrefixes());
        //$prefixes = $this->controllersPrefixes();

        foreach($prefixes as $prefix) {
            /* @var $class Controller */
            $class = $prefix . 'Controllers\\' . $name . '\Controller';

            if (class_exists($class)) {
                return $class::create($this->appName(), $name, $request, $view);
            }
        }

        return false;
    }

    public function loadModel($name)
    {
        $prefixes = array_merge([''], $this->modelsPrefixes());

        foreach($prefixes as $prefix) {
            /* @var $class Internal */
            $class = $prefix . 'Model\\' . $name;

            if (class_exists($class)) {
                return $class::newInstance($this->appName());
            }
        }

        throw Exception::newInstance($this->appName(), 'Model `' . $name . '` not found.');
    }

    /**
     * @param ClassLoader $loader
     * @return ClassLoader|bool
     */
    public function loader(ClassLoader $loader = null)
    {
        return $this->memory('loader', ...func_get_args());
    }

    /**
     * @param Binder $binder
     * @return Binder|bool
     */
    public function binder(Binder $binder = null)
    {
        return $this->memory('binder', ...func_get_args());
    }

    /**
     * @param Listener $listener
     * @return Listener|bool
     */
    public function listener(Listener $listener = null)
    {
        return $this->memory('listener', ...func_get_args());
    }

    /**
     * @param Response $response
     * @return Response|bool
     */
    public function response(Response $response = null)
    {
        return $this->memory('response', ...func_get_args());
    }

    /**
     * @param Translator $translator
     * @return Translator|bool
     */
    public function translator(Translator $translator = null)
    {
        return $this->memory('translator', ...func_get_args());
    }

    /**
     * @param Resources $resources
     * @return Resources|bool
     */
    public function resources(Resources $resources = null)
    {
        return $this->memory('resources', ...func_get_args());
    }

    /**
     * @param Components $components
     * @return Components|bool
     */
    public function components(Components $components = null)
    {
        return $this->memory('components', ...func_get_args());
    }

    /**
     * @param Dispatcher $router
     * @return Dispatcher|bool
     */
    public function router(Dispatcher $router = null)
    {
        return $this->memory('router', ...func_get_args());
    }

    public function viewPaths($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function controllersPrefixes($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function modelsPrefixes($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function extended($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }
}
