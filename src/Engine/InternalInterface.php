<?php

namespace Greg\Engine;

use Greg\Support\Obj;

interface InternalInterface
{
    /**
     * @param string $appName
     * @return static
     * @throws \Exception
     */
    static public function create($appName = 'default');

    public function &memory($key = null, $value = null);

    /**
     * @return \Greg\Application\Runner
     */
    public function app();

    public function appName($value = null, $type = Obj::VAR_REPLACE);
}