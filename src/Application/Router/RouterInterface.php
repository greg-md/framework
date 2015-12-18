<?php

namespace Greg\Application\Router;

interface RouterInterface
{
    const EVENT_DISPATCHING = 'router.dispatching';

    const EVENT_DISPATCHED = 'router.dispatched';
}