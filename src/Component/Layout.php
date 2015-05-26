<?php

namespace Greg\Component;

use Greg\Application\Runner;
use Greg\Engine\Internal;
use Greg\Event\Listener;
use Greg\Event\SubscriberInterface;
use Greg\Event\SubscriberTrait;
use Greg\Router\Route;
use Greg\Support\Obj;
use Greg\View\Viewer;

class Layout implements SubscriberInterface
{
    use SubscriberTrait, Internal;

    const EVENT_DISPATCHING = 'layout.dispatching';

    const EVENT_DISPATCHED = 'layout.dispatched';

    protected $renderName = 'layout';

    protected $disabled = false;

    public function subscribe(Listener $listener)
    {
        $listener->register([
            Runner::EVENT_DISPATCHING,
            Runner::EVENT_DISPATCHED,
        ], $this);

        return $this;
    }

    public function appDispatching(Listener $listener)
    {
        if (!$this->disabled()) {
            $listener->fire(static::EVENT_DISPATCHING);
        }

        return $this;
    }

    public function appDispatched(Route $route, &$response, Viewer $viewer, Listener $listener)
    {
        if (!$this->disabled()) {
            $response = $viewer->renderName($this->renderName(), [
                'body' => $response,
            ]);

            $listener->fireRef(static::EVENT_DISPATCHED, $response);
        }

        return $this;
    }

    public function renderName($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function disabled($value = null)
    {
        return Obj::fetchBoolVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }
}