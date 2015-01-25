<?php

namespace Greg\Router;

use Greg\Engine\Internal;
use Greg\Support\Obj;

abstract class Route implements RouteInterface
{
    use Internal;

    protected $name = null;

    protected $format = null;

    protected $param = [];

    protected $extend = true;

    protected $callback = null;

    public function __construct($name, $format, $options = [])
    {
        $this->name($name);

        $this->format($format);

        if (is_array($options)) {
            $this->setOptions($options);
        }

        if (($options instanceof \Closure)) {
            $this->callback($options);
        }

        return $this;
    }

    public function setOptions($options)
    {
        foreach($options as $key => $value) {
            $this->setOption($key, $value);
        }

        return $this;
    }

    public function setOption($key, $value = null)
    {
        switch($key) {
            case 'param':
            case 'extend':
            case 'callback':
                $this->{$key}($value);
                break;
        }

        return $this;
    }

    static public function getPathParts($path)
    {
        $path = trim($path, '/');

        return $path ? explode('/', $path) : [];
    }

    static public function pathToParam($path)
    {
        return static::partsToParam(static::getPathParts($path));
    }

    static public function partsToParam($parts)
    {
        $param = [];

        foreach (array_chunk($parts, 2) as $value) {
            $param[$value[0]] = array_key_exists(1, $value) ? $value[1] : null;
        }

        return $param;
    }

    protected function paramException($param)
    {
        throw Exception::create($this->appName(), 'Parameter `' . $param
            . '` is required in router `' . $this->name() . '`.');
    }

    public function name($value = null, $type = Obj::VAR_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function format($value = null, $type = Obj::VAR_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function param($key = null, $value = null, $type = Obj::VAR_APPEND, $recursive = false, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function extend($value = null)
    {
        return Obj::fetchBoolVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    /**
     * @param callable $value
     * @return \Closure|null
     */
    public function callback(\Closure $value = null)
    {
        return Obj::fetchVar($this, $this->{__FUNCTION__}, func_get_args());
    }
}