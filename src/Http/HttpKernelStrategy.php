<?php

namespace Greg\Http;

use Greg\ApplicationStrategy;
use Greg\Router\Router;
use Greg\Support\Http\Response;

interface HttpKernelStrategy
{
    const EVENT_RUN = 'http.run';

    const EVENT_DISPATCHING = 'http.dispatching';

    const EVENT_DISPATCHED = 'http.dispatched';

    const EVENT_FINISHED = 'http.finished';

    /**
     * @param string $path
     *
     * @return Response
     */
    public function run($path = '/');

    public function addControllersPrefix($prefix);

    public function addControllersPrefixes(array $prefixes);

    public function setControllersPrefixes(array $prefixes);

    public function getControllersPrefixes();

    /**
     * @return ApplicationStrategy
     */
    public function app();

    /**
     * @return Router
     */
    public function router();
}
