<?php

namespace Greg\Console;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleKernel
{
    const EVENT_INIT = 'http.init';

    const EVENT_RUN = 'http.run';

    const EVENT_DISPATCHING = 'http.dispatching';

    const EVENT_DISPATCHED = 'http.dispatched';

    const EVENT_FINISHED = 'http.finished';

    protected $app = null;

    protected $consoleName = 'Greg Application';

    protected $consoleVersion = '1.0.0';

    protected $commands = [];

    public function __construct(\Greg\Application $app)
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

    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        $listener = $this->app->getListener();

        $listener->fireWith(static::EVENT_RUN);

        $app = new Application($this->consoleName, $this->consoleVersion);

        $this->loadCommands($app);

        $response = $app->run($input, $output);

        $listener->fireWith(static::EVENT_FINISHED);

        return $response;
    }

    protected function loadCommands(Application $app)
    {
        foreach ($this->commands as $command) {
            $app->add($command);
        }

        return $this;
    }
}
