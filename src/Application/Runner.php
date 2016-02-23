<?php

namespace Greg\Application;

use Greg\Application\Engine\Binder;
use Greg\Application\Engine\InternalTrait;
use Greg\Application\Event\Listener;
use Greg\Application\Http\Request;
use Greg\Application\Http\Response;
use Greg\Application\Router\Router;
use Greg\Loader\ClassLoader;
use Greg\Server\Server;
use Greg\Server\ServerConfig;
use Greg\Server\ServerIni;
use Greg\Server\Session;
use Greg\Storage\AccessorTrait;
use Greg\Storage\ArrayAccessTrait;
use Greg\Tool\Arr;
use Greg\Tool\Obj;
use Greg\Tool\Str;
use Greg\Translation\Translator;
use Greg\Application\View\Viewer;

class Runner implements \ArrayAccess
{
    use AccessorTrait, ArrayAccessTrait, InternalTrait;

    const EVENT_INIT = 'app.init';

    const EVENT_RUN = 'app.run';

    const EVENT_ROUTER_DISPATCHING = 'app.router.dispatching';

    const EVENT_ROUTER_DISPATCHED = 'app.router.dispatched';

    const EVENT_DISPATCHED = 'app.dispatched';

    const EVENT_ERROR_DISPATCHED = 'app.error.dispatched';

    const EVENT_FINISHED = 'app.finished';

    protected $controllersPrefixes = [];

    protected $extended = [];

    protected $path = null;

    public function __construct(array $settings = [], $appName = null)
    {
        if ($appName !== null) {
            $this->appName($appName);
        }

        $this->memory('app', $this);

        $this->storage(Arr::fixIndexes($settings));

        return $this;
    }

    public function init()
    {
        $this->initConfig();

        $this->initServer();

        $this->initBinder();

        // Add staffs to Binder
        $this->binder()->setObjects([$this]);

        $this->initLoader();

        $this->initListener();

        $this->initSession();

        $this->initTranslator();

        $this->initViewer();

        $this->initRouter();

        $this->initComponents();

        // Fire app init event
        $this->listener()->fire(static::EVENT_INIT);

        return $this;
    }

    public function initConfig()
    {
        $this->controllersPrefixes($this->getIndexArray('controllers.prefixes'));

        $this->extended($this->getArray('extended'));

        return $this;
    }

    public function initServer()
    {
        // Server ini
        if ($serverIni = $this->getIndexArray('server.ini')) {
            ServerIni::param($serverIni);
        }

        // Server config
        if ($serverConfig = $this->getIndexArray('server.config')) {
            ServerConfig::param($serverConfig);
        }

        // Session id
        if ($sessionId = $this->getIndex('session.id')) {
            Session::id($sessionId);
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
        $this->binder($model = Binder::newInstance($this->appName()));

        // Add Binder to Binder
        $model->setObject($model);

        if ($singletons = $this->getIndexArray('binder.singletons')) {
            $model->singletons($singletons);
        }

        if ($instancesPrefixes = $this->getIndexArray('binder.instances_prefixes')) {
            $model->instancesPrefixes($instancesPrefixes);
        }

        return $this;
    }

    public function getBinder()
    {
        if (!$model = $this->binder()) {
            throw new \Exception('Application binder was not initiated.');
        }

        return $model;
    }

    public function initLoader()
    {
        // Load ClassLoader
        $this->loader($model = new ClassLoader($this->getIndexArray('loader.paths')));

        // Register the ClassLoader to autoload
        $model->register(true);

        // Add ClassLoader to Binder
        $this->binder()->setObject($model);

        return $this;
    }

    public function getLoader()
    {
        if (!$model = $this->loader()) {
            throw new \Exception('Application loader was not initiated.');
        }

        return $model;
    }

    public function initListener()
    {
        // Load Listener
        $this->listener($model = Listener::create($this->appName(),
                                    $this->getIndexArray('listener.events'),
                                    $this->getIndexArray('listener.subscribers')));

        // Add Listener to Binder
        $this->binder()->setObject($model);

        return $this;
    }

    public function getListener()
    {
        if (!$model = $this->listener()) {
            throw new \Exception('Application listener was not initiated.');
        }

        return $model;
    }

    public function initSession()
    {
        // Load Session
        $this->session($model = new Session());

        // Add Session to Binder
        $this->binder()->setObject($model);

        return $this;
    }

    public function initTranslator()
    {
        // Load Translator
        $this->translator($model = new Translator(
                            $this->getIndexArray('translator.languages'),
                            $this->getIndexArray('translator.translates')));

        // Add Translator to Binder
        $this->binder()->setObject($model);

        return $this;
    }

    public function getTranslator()
    {
        if (!$model = $this->translator()) {
            throw new \Exception('Application translator was not initiated.');
        }

        return $model;
    }

    public function initViewer()
    {
        // Load Translator
        $this->viewer($model = Viewer::create($this->appName(),
                        $this->getIndexArray('viewer.paths'),
                        $this->getIndexArray('viewer.params')));

        // Add Viewer to Binder
        $this->binder()->setObject($model);

        return $this;
    }

    public function getViewer()
    {
        if (!$model = $this->viewer()) {
            throw new \Exception('Application viewer was not initiated.');
        }

        return $model;
    }

    public function initRouter()
    {
        // Load Dispatcher
        $this->router($model = Router::create($this->appName(), $this->getIndexArray('router.routes'), $this->getIndexArray('router.onError')));

        // Add Dispatcher to Binder
        $this->binder()->setObject($model);

        return $this;
    }

    public function getRouter()
    {
        if (!$model = $this->router()) {
            throw new \Exception('Application router was not initiated.');
        }

        return $model;
    }

    public function initComponents()
    {
        // Load Components
        $this->components($model = Components::create($this->appName(), $this->getArray('components')));

        // Add Components to Binder
        $this->binder()->setObject($model);

        return $this;
    }

    public function getComponents()
    {
        if (!$model = $this->components()) {
            throw new \Exception('Application components was not initiated.');
        }

        return $model;
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

        /* @var $class InternalTrait */
        $class = $binder ? $binder->loadInstanceArgs($className, $args) : Obj::loadInstanceArgs($className, $args);

        if (method_exists($class, 'appName')) {
            $class->appName($this->appName());
        }

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

            /* @var $instance InternalTrait */
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

    /**
     * @param string $path
     * @return Response
     */
    public function run($path = '/')
    {
        if (!func_num_args()) {
            $path = Request::relativeUriPath();
        } else {
            $path = $path ?: '/';
        }

        $this->path($path);

        $this->listener()->fireRef(static::EVENT_RUN);

        $response = $this->router()->dispatchPath($path, $route, [
            Router::EVENT_DISPATCHING => static::EVENT_ROUTER_DISPATCHING,
            Router::EVENT_DISPATCHED => static::EVENT_ROUTER_DISPATCHED,
        ]);

        if (Str::isScalar($response)) {
            $response = Response::create($this->appName(), $response);
        }

        // finish with layout request
        $this->listener()->fireWith(static::EVENT_DISPATCHED, $response, $route);

        $this->listener()->fire(static::EVENT_FINISHED);

        return $response;
    }

    public function action($name = null, $controllerName = null, array $params = [], ...$others)
    {
        $name = $name ?: 'index';

        $controllerName = $controllerName ?: 'base';

        Arr::bringRef($controllerName);

        $controller = $this->loadController($controllerName);

        $actionMethod = Str::phpName($name);

        if (!method_exists($controller, $actionMethod)) {
            throw new \Exception('Action `' . $name . '` not found in controller `' . implode('/', $controllerName) . '`.');
        }

        $request = Request::create($this->appName(), $params);

        return $this->binder()->callWith([$controller, $actionMethod], $request, ...array_values($params), ...$others);
    }

    public function loadController($name)
    {
        Arr::bringRef($name);

        /* @var $class InternalTrait */
        if ($class = $this->controllerExists($name)) {
            return $class::newInstance($this->appName());
        }

        throw new \Exception('Controller `' . implode('/', $name) . '` not found.');
    }

    public function controllerExists($name)
    {
        return Obj::classExists($name, array_merge($this->controllersPrefixes(), ['']), 'Controllers\\');
    }

    public function once($name, callable $callable)
    {
        $this->setIndex('once.' . $name, $callable);

        return $this;
    }

    public function loadOnce($name)
    {
        $callable = $this->getIndex('once.' . $name);

        if ($callable) {
            $this->set($name, $this->binder()->call($callable));
        }

        return $this;
    }

    public function get($key, $else = null)
    {
        if (!$this->has($key)) {
            $this->loadOnce($key);
        }

        return Arr::get($this->accessor(), $key, $else);
    }

    public function &offsetGet($key)
    {
        if (!$this->has($key)) {
            $this->loadOnce($key);
        }

        return $this->accessor()[$key];
    }

    public function basePath()
    {
        return $this['base_path'] ?: realpath(Server::documentRoot() . '/..');
    }

    public function publicPath()
    {
        return $this['public_path'] ?: Server::documentRoot();
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
     * @param Session $session
     * @return Session|bool
     */
    public function session(Session $session = null)
    {
        return $this->memory('session', ...func_get_args());
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
     * @param Viewer $translator
     * @return Viewer|bool
     */
    public function viewer(Viewer $translator = null)
    {
        return $this->memory('viewer', ...func_get_args());
    }

    /**
     * @param Components $components
     * @return Components|bool
     */
    public function components(Components $components = null)
    {
        return $this->memory('components', ...func_get_args());
    }

    public function getLastRunPath()
    {
        return $this->path();
    }

    /**
     * @param Router $router
     * @return Router|bool
     */
    public function router(Router $router = null)
    {
        return $this->memory('router', ...func_get_args());
    }

    public function controllersPrefixes($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function extended($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    protected function path($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }
}
