<?php

namespace Greg\Db\Sql\Table;

use Greg\Support\Obj;

trait Schema
{
    abstract protected function loadSchema();

    abstract public function addColumns(array $columns);

    abstract public function name($value = null, $type = Obj::VAR_REPLACE);

    abstract public function columns($key = null, $value = null, $type = Obj::VAR_APPEND, $replace = false);

    abstract public function autoIncrement($value = null, $type = Obj::VAR_REPLACE);

    abstract public function primary($key = null, $value = null, $type = Obj::VAR_APPEND, $replace = false);

    abstract public function unique($key = null, $value = null, $type = Obj::VAR_APPEND, $replace = false);

    abstract public function references($key = null, $value = null, $type = Obj::VAR_APPEND, $replace = false);

    abstract public function relationships($key = null, $value = null, $type = Obj::VAR_APPEND, $replace = false);

    abstract public function dependencies($key = null, $value = null, $type = Obj::VAR_APPEND, $replace = false);

    abstract public function appName($value = null, $type = Obj::VAR_REPLACE);
}