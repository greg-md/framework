<?php

namespace Greg;

interface ApplicationStrategy extends \ArrayAccess
{
    const EVENT_RUN = 'app.run';

    const EVENT_FINISHED = 'app.finished';

    public function __construct(array $config = []);

    public function addComponent($component, $name = null);

    public function basePath();

    public function publicPath();

    public function debugMode();

    public function run(callable $callable);

    public function on($event, $listener);

    public function fire($event, ...$args);

    public function fireRef($event, &...$args);

    public function fireWith($event, ...$args);

    public function fireWithRef($event, &...$args);

    public function scope(callable $callable);

    /**
     * @return Config
     */
    public function config();

    /**
     * @return IoCContainer
     */
    public function ioc();
}
