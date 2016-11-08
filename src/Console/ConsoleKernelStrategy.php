<?php

namespace Greg\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface ConsoleKernelStrategy
{
    const EVENT_RUN = 'console.run';

    const EVENT_FINISHED = 'console.finished';

    public function run(InputInterface $input = null, OutputInterface $output = null);

    public function app();

    public function consoleApp();
}
