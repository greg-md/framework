<?php

namespace Greg\Db\Sql;

use Greg\Engine\Internal;
use Greg\Engine\InternalInterface;

abstract class Storage implements StorageInterface, InternalInterface
{
    const PARAM_BOOL = 5;

    const PARAM_NULL = 0;

    const PARAM_INT = 1;

    const PARAM_STR = 2;

    const PARAM_LOB = 3;

    const PARAM_STMT = 4;

    const FETCH_ORI_NEXT = 0;

    use StorageTrait, Internal;
}