<?php

namespace Greg\Framework;

use PHPUnit\Framework\TestCase;

class BootstrapTest extends TestCase
{
    public function testCanDefineApplication()
    {
        /** @var BootstrapAbstract $bootstrap */
        $bootstrap = new class() extends BootstrapAbstract {
        };

        $bootstrap->setApplication(new Application());

        $this->assertInstanceOf(Application::class, $bootstrap->getApplication());
    }

    public function testCanGetApplication()
    {
        /** @var BootstrapAbstract $bootstrap */
        $bootstrap = new class() extends BootstrapAbstract {
        };

        $bootstrap->setApplication(new Application());

        $this->assertInstanceOf(Application::class, $bootstrap->app());
    }

    public function testCanThrowExceptionIfApplicationIsNotDefined()
    {
        /** @var BootstrapAbstract $bootstrap */
        $bootstrap = new class() extends BootstrapAbstract {
        };

        $this->expectException(\Exception::class);

        $bootstrap->app();
    }

    public function testCanBootWithDependencies()
    {
        /** @var BootstrapAbstract $bootstrap */
        $bootstrap = new class() extends BootstrapAbstract {
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
        /** @var BootstrapAbstract $bootstrap */
        $bootstrap = new class() extends BootstrapAbstract {
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
        /** @var BootstrapAbstract $bootstrap */
        $bootstrap = new class() extends BootstrapAbstract {
            public function bootFoo()
            {
                $this->dependsOn('bar');
            }
        };

        $this->expectException(\Exception::class);

        $bootstrap->boot(new Application());
    }
}
