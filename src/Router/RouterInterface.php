<?php

namespace Greg\Router;

interface RouterInterface
{
    const EVENT_DISPATCH = 'router.dispatch';

    const EVENT_DISPATCHING = 'router.dispatching';

    const EVENT_DISPATCHED = 'router.dispatched';
}