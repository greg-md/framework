<?php

namespace Greg\Framework;

interface ApplicationContract extends \ArrayAccess
{
    const EVENT_RUN = 'app.run';

    const EVENT_FINISHED = 'app.finished';

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

    public function config();

    public function ioc();
}
