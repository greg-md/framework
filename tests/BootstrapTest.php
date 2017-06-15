<?php

namespace Greg\Framework;

use PHPUnit\Framework\TestCase;

class BootstrapTest extends TestCase
{
    public function testCanDefineApplication()
    {
        /** @var Bootstrap $bootstrap */
        $bootstrap = new class() extends Bootstrap {
        };

        $bootstrap->setApplication(new Application());

        $this->assertInstanceOf(Application::class, $bootstrap->getApplication());
    }

    public function testCanGetApplication()
    {
        /** @var Bootstrap $bootstrap */
        $bootstrap = new class() extends Bootstrap {
        };

        $bootstrap->setApplication(new Application());

        $this->assertInstanceOf(Application::class, $bootstrap->app());
    }

    public function testCanThrowExceptionIfApplicationIsNotDefined()
    {
        /** @var Bootstrap $bootstrap */
        $bootstrap = new class() extends Bootstrap {
        };

        $this->expectException(\Exception::class);

        $bootstrap->app();
    }

    public function testCanBootWithDependencies()
    {
        /** @var Bootstrap $bootstrap */
        $bootstrap = new class() extends Bootstrap {
            public function bootFoo()
            {
                $this->dependsOn('bar');
            }

            public function bootBar()
            {
            }
        };

        $bootstrap->boot(new Application());

        $this->assertEquals(['bar', 'foo'], $bootstrap->booted());
    }

    public function testCanNotBootIfBooted()
    {
        /** @var Bootstrap $bootstrap */
        $bootstrap = new class() extends Bootstrap {
            public function bootBar()
            {
            }

            public function bootFoo()
            {
                $this->dependsOn('bar');
            }
        };

        $bootstrap->boot(new Application());

        $this->assertEquals(['bar', 'foo'], $bootstrap->booted());
    }

    public function testCanThrowExceptionIfDependencyNotExists()
    {
        /** @var Bootstrap $bootstrap */
        $bootstrap = new class() extends Bootstrap {
            public function bootFoo()
            {
                $this->dependsOn('bar');
            }
        };

        $this->expectException(\Exception::class);

        $bootstrap->boot(new Application());
    }
}
