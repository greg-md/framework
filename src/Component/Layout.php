<?php

namespace Greg\Component;

use Greg\Application\Runner;
use Greg\Engine\Internal;
use Greg\Event\Listener;
use Greg\Event\SubscriberInterface;
use Greg\Event\SubscriberTrait;
use Greg\Http\Request;
use Greg\Http\Response;
use Greg\Router\Dispatcher;
use Greg\Support\Obj;
use Greg\View\Viewer;

class Layout implements SubscriberInterface
{
    use SubscriberTrait, Internal;

    const EVENT_STARTUP = 'layout.startup';

    const EVENT_BEFORE_DISPATCH = 'layout.before.dispatch';

    const EVENT_AFTER_DISPATCH = 'layout.after.dispatch';

    protected $body = null;

    protected $renderName = 'layout';

    protected $disabled = false;

    protected $request = null;

    protected $view = null;

    public function subscribe(Listener $listener)
    {
        $listener->register([
            Runner::EVENT_STARTUP,
            Runner::EVENT_DISPATCH,
        ], $this);

        return $this;
    }

    public function appStartup(Dispatcher $router, Listener $listener)
    {
        if (!$this->disabled()) {
            $this->request($request = Request::create($this->appName(), $router->param()));

            $this->view($view = $this->app()->newView($request));

            $listener->fire(static::EVENT_STARTUP);
        }

        return $this;
    }

    public function appDispatch(Listener $listener, Response $response)
    {
        if (!$this->disabled()) {
            $listener->fire(static::EVENT_BEFORE_DISPATCH);

            $this->body($response->body());

            $data = $this->view()->renderName($this->renderName());

            $response->body($data);

            $listener->fire(static::EVENT_AFTER_DISPATCH);
        }

        return $this;
    }

    public function body($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function renderName($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function disabled($value = null)
    {
        return Obj::fetchBoolVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    /**
     * @param Request $value
     * @return Request|$this|null
     */
    public function request(Request $value = null)
    {
        return Obj::fetchVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    /**
     * @param Viewer $value
     * @return Viewer|$this|null
     */
    public function view(Viewer $value = null)
    {
        return Obj::fetchVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }
}