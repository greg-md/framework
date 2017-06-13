<?php

namespace Greg\Framework\Console;

use Greg\Framework\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleKernel
{
    const EVENT_RUN = 'console.run';

    const EVENT_FINISHED = 'console.finished';

    private $app = null;

    private $console = null;

    public function __construct(Application $app = null, \Symfony\Component\Console\Application $console = null)
    {
        $this->app = $app ?: new Application();

        $this->console = $console ?: new \Symfony\Component\Console\Application();

        $this->console->setAutoExit(false);

        $this->boot();

        return $this;
    }

    public function app(): Application
    {
        return $this->app;
    }

    public function console(): \Symfony\Component\Console\Application
    {
        return $this->console;
    }

    public function run(InputInterface $input = null, OutputInterface $output = null): int
    {
        $this->app->fire(static::EVENT_RUN, $this->console);

        $response = $this->console->run($input, $output);

        $this->app->fire(static::EVENT_FINISHED, $this->console, $response);

        return $response;
    }

    protected function boot()
    {
    }
}
