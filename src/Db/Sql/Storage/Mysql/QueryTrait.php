<?php

namespace Greg\Db\Sql\Storage\Mysql;

use Greg\Support\Arr;

trait QueryTrait
{
    public function concat($array, $delimiter = '')
    {
        return sizeof($array) > 1 ? 'concat_ws("' . $delimiter . '", ' . implode(', ', $array) . ')' : Arr::first($array);
    }
}