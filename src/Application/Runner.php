<?php

namespace Greg\Application;

use Greg\Engine\Internal;
use Greg\Event\Listener;
use Greg\Event\SubscriberInterface;
use Greg\Http\Request;
use Greg\Http\Response;
use Greg\Router\Dispatcher;
use Greg\Server\AutoLoader;
use Greg\Server\Config as ServerConfig;
use Greg\Server\Ini as ServerIni;
use Greg\Server\Session as ServerSession;
use Greg\Storage\ArrayIndexAccess;
use Greg\Support\Obj;
use Greg\Support\Str;
use Greg\View\Viewer;
use Closure;

class Runner implements \ArrayAccess
{
    use ArrayIndexAccess, Internal;

    const EVENT_INIT = 'app.init';
    const EVENT_RUN = 'app.run';
    const EVENT_STARTUP = 'app.startup';
    const EVENT_DISPATCH = 'app.dispatch';
    const EVENT_FINISH = 'app.finish';

    public function __construct($settings = [], $appName = null)
    {
        if ($appName) {
            $this->appName($appName);
        }

        $this->memory('app', $this);

        $this->replace($settings);

        $this->init();

        return $this;
    }

    public function init()
    {
        if ($serverIni = (array)$this->get('server.ini')) {
            ServerIni::param($serverIni);
        }

        if ($serverConfig = (array)$this->get('server.config')) {
            ServerConfig::param($serverConfig);
        }

        if ($sessionIni = (array)$this->indexGet('session.ini')) {
            ServerSession::ini($sessionIni);
        }

        if ($this->indexHas('session.persistent')) {
            ServerSession::persistent((bool)$this->indexGet('session.persistent'));
        }

        //$this->autoLoader($autoLoader = AutoLoader::create($this->appName(), $this->get('autoLoader.paths')));

        $this->binder($binder = Binder::create($this->appName()));

        $binder->add($this);

        $binder->add($binder);

        $this->listener($listener = Listener::create($this->appName()));

        $binder->add($listener);

        $this->response($response = Response::create($this->appName()));

        $binder->add($response);

        $this->router($router = Dispatcher::create($this->appName()));

        $binder->add($router);

        if ($subscribers = (array)$this->get('subscribers')) {
            $listener->addSubscribers($subscribers);
        }

        if ($components = (array)$this->get('components')) {
            $this->addComponents($components);
        }

        $listener->fire(static::EVENT_INIT);

        return $this;
    }

    public function run($path = '/')
    {
        if (!func_num_args() and $routeRequestParam = $this->get('route.request.param')) {
            $path = Request::getRequest($routeRequestParam);
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
            throw new Exception('Undefined component `' . $name . '`.');
        }

        return $component;
    }

    public function newClass($class)
    {
        $args = func_get_args();

        array_shift($args);

        if (is_bool($class)) {
            $loadExtended = $class;
            $class = array_shift($args);
        } else {
            $loadExtended = true;
        }

        $class = $loadExtended ? $this->getExtended($class) : $class;

        return $this->binder() ? $this->binder()->newClass($class, $args) : Obj::instanceArgs($class, $args);
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
        return Viewer::create($this->appName(), $request, (array)$this->get('view.paths'), $param);
    }

    public function loadController($name, $request, $view)
    {
        $name = Str::phpName($name);

        foreach(array_merge([''], (array)$this->get('controllers.namespaces')) as $namespace) {
            /* @var $class Controller */
            $class = $namespace . '\Controllers\\' . $name . '\Controller';
            if (class_exists($class)) {
                return $class::create($this->appName(), $name, $request, $view);
            }
        }

        return false;
    }

    /**
     * @param AutoLoader $autoLoader
     * @return AutoLoader
     */
    //public function autoLoader(AutoLoader $autoLoader = null)
    //{
    //    return func_num_args() ? $this->memory('autoLoader', $autoLoader) : $this->memory('autoLoader');
    //}

    /**
     * @param Binder $binder
     * @return Binder
     */
    public function binder(Binder $binder = null)
    {
        return func_num_args() ? $this->memory('binder', $binder) : $this->memory('binder');
    }

    /**
     * @param Listener $listener
     * @return Listener
     */
    public function listener(Listener $listener = null)
    {
        return func_num_args() ? $this->memory('listener', $listener) : $this->memory('listener');
    }

    /**
     * @param Response $response
     * @return Response
     */
    public function response(Response $response = null)
    {
        return func_num_args() ? $this->memory('response', $response) : $this->memory('response');
    }

    /**
     * @param Dispatcher $router
     * @return Dispatcher
     */
    public function router(Dispatcher $router = null)
    {
        return func_num_args() ? $this->memory('router', $router) : $this->memory('router');
    }
}