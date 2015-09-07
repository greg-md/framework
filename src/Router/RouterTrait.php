<?php

namespace Greg\Router;

use Greg\Application\Runner;
use Greg\Support\Tool\Arr;
use Greg\Support\Tool\Obj;

trait RouterTrait
{
    protected function newRoute($name, $format, $type = null)
    {
        return Route::create($this->appName(), $name, $format, $type);
    }

    public function dispatchPath($path, &$foundRoute = null, array $events = [])
    {
        $listener = $this->app()->listener();

        foreach($this->getRoutes() as $route) {
            if ($matchedRoute = $route->match($path)) {
                $foundRoute = $matchedRoute;

                $listener->fireWith(RouterInterface::EVENT_DISPATCHING, $matchedRoute);

                if (Arr::hasRef($events, RouterInterface::EVENT_DISPATCHING)) {
                    $listener->fireWith($events[RouterInterface::EVENT_DISPATCHING], $matchedRoute);
                }

                $content = $matchedRoute->dispatch();

                $listener->fireWith(RouterInterface::EVENT_DISPATCHED, $matchedRoute);

                if (Arr::hasRef($events, RouterInterface::EVENT_DISPATCHED)) {
                    $listener->fireWith($events[RouterInterface::EVENT_DISPATCHED], $matchedRoute);
                }

                return $content;
            }
        }

        return null;
    }

    /**
     * @return Route[]
     */
    abstract public function getRoutes();

    abstract public function appName($value = null, $type = Obj::PROP_REPLACE);

    /**
     * @param Runner $runner
     * @return Runner|null
     */
    abstract public function app(Runner $runner = null);
}