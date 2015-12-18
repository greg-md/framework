<?php

namespace Greg\Application\Engine;

use Greg\Tool\Arr;
use Greg\Tool\Obj;

class Binder extends \Greg\Engine\Binder
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
        return Arr::has($this->instancesPrefixes(), function($value) use ($className) {
            return strpos($className, $value) === 0;
        });
    }

    public function instancesPrefixes($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }
}