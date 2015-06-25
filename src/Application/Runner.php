<?php

namespace Greg\Application;

use Greg\Composer\Autoload\ClassLoader;
use Greg\Support\Engine\Internal;
use Greg\Event\Listener;
use Greg\Http\Request;
use Greg\Http\Response;
use Greg\Router\Dispatcher;
use Greg\Support\Server\Config;
use Greg\Support\Server\Ini;
use Greg\Support\Server\Session;
use Greg\Support\Storage\Accessor;
use Greg\Support\Storage\ArrayAccess;
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

    const EVENT_DISPATCHING = 'app.dispatching';

    const EVENT_ROUTER_DISPATCH = 'app.router.dispatch';

    const EVENT_ROUTER_DISPATCHING = 'app.router.dispatching';

    const EVENT_ROUTER_DISPATCHED = 'app.router.dispatched';

    const EVENT_DISPATCHED = 'app.dispatched';

    const EVENT_ERROR_DISPATCHED = 'app.error.dispatched';

    const EVENT_FINISHED = 'app.finished';

    protected $controllersPrefixes = [];

    protected $extended = [];

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

        // Load Helper
        $this->helper($helper = Helper::newInstance($this->appName()));

        $this->initBinder();

        // Add staffs to Binder
        $this->binder()->setObjects([$this, $helper]);

        $this->initLoader();

        $this->initListener();

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

    public function getHelper()
    {
        if (!$model = $this->helper()) {
            throw new \Exception('Application helper was not initiated.');
        }

        return $model;
    }

    public function initBinder()
    {
        // Load Binder
        $this->binder($model = Binder::create($this->appName()));

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
        $this->loader($model = ClassLoader::create($this->appName(), $this->getIndexArray('loader.paths')));

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

    public function initTranslator()
    {
        // Load Translator
        $this->translator($model = Translator::create($this->appName(),
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
        $this->router($model = Dispatcher::create($this->appName(), $this->getArray('router.routes')));

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

    /**
     * @param string $path
     * @return Response
     */
    public function run($path = '/')
    {
        if (!func_num_args()) {
            list($path) = explode('?', Request::uri(), 2);
        }

        $path = $path ?: '/';

        $this->listener()->fireRef(static::EVENT_RUN, $path);

        $this->listener()->fire(static::EVENT_DISPATCHING);

        $response = $this->router()->dispatch($path, [
            Dispatcher::EVENT_DISPATCH => static::EVENT_ROUTER_DISPATCH,
            Dispatcher::EVENT_DISPATCHING => static::EVENT_ROUTER_DISPATCHING,
            Dispatcher::EVENT_DISPATCHED => static::EVENT_ROUTER_DISPATCHED,
        ], $route);

        if (is_scalar($response)) {
            $response = Response::create($this->appName(), $response);
        }

        // finish with layout request
        $this->listener()->fire(static::EVENT_DISPATCHED, $response, $route);

        $this->listener()->fire(static::EVENT_FINISHED);

        return $response;
    }

    protected function dispatchError(\Exception $exception, array $params = [])
    {
        if ($error = $this->getIndexArray('error')) {
            return $this->router()->createRoute('error', '', null, $error)->dispatch([
                    'exception' => $exception,
                ] + $params, false);
        }

        throw $exception;
    }

    public function action($name = null, $controllerName = null, array $params = [], $catchException = true)
    {
        $name = $name ?: 'index';

        $controllerName = $controllerName ?: 'base';

        Arr::bringRef($controllerName);

        try {
            $controller = $this->loadController($controllerName);

            $actionName = Str::phpName($name);

            if (!method_exists($controller, $actionName)) {
                throw new \Exception('Action `' . $name . '` not found in controller `' . implode('/', $controllerName) . '`.');
            }

            $request = Request::create($this->appName(), $params);

            return $this->binder()->callWith([$controller, $actionName], $request);
        } catch (\Exception $e) {
            if ($catchException) {
                return $this->dispatchError($e, [
                    'from' => 'app.action',
                ]);
            }

            throw $e;
        }
    }

    public function loadController($name)
    {
        Arr::bringRef($name);

        /* @var $class Internal */
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

    public function &get($key, $else = null)
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

    /**
     * @param Helper $binder
     * @return Helper|bool
     */
    public function helper(Helper $binder = null)
    {
        return $this->memory('helper', ...func_get_args());
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

    /**
     * @param Dispatcher $router
     * @return Dispatcher|bool
     */
    public function router(Dispatcher $router = null)
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
}
