<?php

namespace Greg\Console;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleKernel
{
    const EVENT_RUN = 'console.run';

    const EVENT_FINISHED = 'console.finished';

    private $app = null;

    private $consoleApp = null;

    public function __construct(ApplicationContract $app)
    {
        $this->app = $app;

        $this->consoleApp = new Application('Greg Application', '1.0.0');

        $this->boot();

        return $this;
    }

    protected function boot()
    {
    }

    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        $this->app->fireWith(static::EVENT_RUN, $this->consoleApp);

        $response = $this->consoleApp->run($input, $output);

        $this->app->fireWith(static::EVENT_FINISHED, $this->consoleApp);

        return $response;
    }

    public function app()
    {
        return $this->app;
    }

    public function consoleApp()
    {
        return $this->consoleApp;
    }
}
