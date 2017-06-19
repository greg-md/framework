<?php

namespace Greg\Framework\Console;

use Greg\Framework\Application;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleKernelTest extends TestCase
{
    public function testCanInstantiate()
    {
        $kernel = new ConsoleKernel();

        $this->assertInstanceOf(ConsoleKernel::class, $kernel);
    }

    public function testCanGetApplication()
    {
        $kernel = new ConsoleKernel();

        $this->assertInstanceOf(Application::class, $kernel->app());
    }

    public function testCanGetRouter()
    {
        $kernel = new ConsoleKernel();

        $this->assertInstanceOf(\Symfony\Component\Console\Application::class, $kernel->console());
    }

    public function testCanRun()
    {
        $kernel = new ConsoleKernel();

        $kernel->addCommand(new class() extends Command {
            protected function configure()
            {
                $this->setName('hello');
            }

            protected function execute(InputInterface $input, OutputInterface $output)
            {
                $output->write('Hello World!');
            }
        });

        $input = new ArrayInput([
            'command' => 'hello',
        ]);

        $output = new BufferedOutput();

        $this->assertEquals(0, $kernel->run($input, $output));

        $this->assertEquals('Hello World!', $output->fetch());
    }
}
