<?php

namespace Greg\Application;

use Greg\Engine\InternalTrait;
use Greg\Support\Tool\Arr;
use Greg\Support\Tool\Obj;

class Binder extends \Greg\Support\Application\Binder
{
    use InternalTrait;

    protected $instancesPrefixes = [];

    public function get($name)
    {
        $object = parent::get($name);

        if (!$object and $this->isInstancePrefix($name)) {
            /* @var $name InternalTrait */
            $object = $name::instance($this->appName());
        }

        return $object;
    }

    public function isInstancePrefix($className)
    {
        $array = $this->instancesPrefixes();

        return Arr::hasRef($array, function($value) use ($className) {
            return strpos($className, $value) === 0;
        });
    }

    public function instancesPrefixes($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }
}