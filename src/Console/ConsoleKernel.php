<?php

namespace Greg\Console;

use Greg\Application;

class ConsoleKernel
{
    const EVENT_INIT = 'http.init';

    const EVENT_RUN = 'http.run';

    const EVENT_DISPATCHING = 'http.dispatching';

    const EVENT_DISPATCHED = 'http.dispatched';

    const EVENT_FINISHED = 'http.finished';

    protected $app = null;

    public function __construct(Application $app)
    {
        $this->app = $app;

        $this->init();

        return $this;
    }

    protected function init()
    {
        $this->loadComponents();

        $this->app->getListener()->fire(static::EVENT_INIT);

        return $this;
    }

    protected function loadComponents()
    {
        foreach ($this->app->config()->getIndexArray('console.components') as $key => $component) {
            $this->app->addComponent($component, $key);
        }

        return $this;
    }

    public function run()
    {
        $listener = $this->app->getListener();

        $listener->fireWith(static::EVENT_RUN);

        $listener->fireWith(static::EVENT_FINISHED);

        return $this;
    }
}