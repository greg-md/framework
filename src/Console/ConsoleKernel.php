<?php

namespace Greg\Framework\Console;

use Greg\Framework\Application;
use Greg\Framework\ServiceProvider;
use Symfony\Component\Console\Command\Command;
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

        $this->bootServiceProviders();

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

    public function bootstrap(ServiceProvider $class)
    {
        $this->app()->bootstrap($class);

        $class->bootConsoleKernel($this);

        return $this;
    }

    public function addCommand($command)
    {
        if (is_scalar($command)) {
            $command = $this->app()->ioc()->load($command);
        }

        if (!($command instanceof Command)) {
            throw new \Exception('Application command should be an instance of `' . Command::class . '`.');
        }

        $this->console()->add($command);
    }

    public function run(InputInterface $input = null, OutputInterface $output = null): int
    {
        return $this->app->run(function () use ($input, $output) {
            $this->app->fire(static::EVENT_RUN, $this->console);

            $response = $this->console->run($input, $output);

            $this->app->fire(static::EVENT_FINISHED, $this->console, $response);

            return $response;
        });
    }

    private function bootServiceProviders()
    {
        foreach ($this->app()->getServiceProviders() as $serviceProvider) {
            $serviceProvider->bootConsoleKernel($this);
        }

        return $this;
    }

    protected function boot()
    {
    }
}
