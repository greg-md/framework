<?php

namespace Greg\Framework;

use Greg\Support\Accessor\ArrayAccessTrait;
use Greg\Support\Arr;

class Config implements \ArrayAccess
{
    use ArrayAccessTrait;

    public function __construct(array $config = [])
    {
        $this->setAccessor(Arr::fixIndexes($config));
    }
}
