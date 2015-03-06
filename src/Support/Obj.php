<?php

namespace Greg\Support;

use Closure;
use Greg\Engine\Internal;
use Greg\Engine\InternalInterface;
use Greg\Storage\ArrayObject;

class Obj
{
    const VAR_APPEND = 'append';

    const VAR_PREPEND = 'prepend';

    const VAR_REPLACE = 'replace';

    static public function instance($className, $_ = null)
    {
        $args = func_get_args();

        array_shift($args);

        return static::instanceArgs($className, $args);
    }

    /**
     * @param $className
     * @param array $args
     * @return object
     */
    static public function instanceArgs($className, array $args = [])
    {
        $class = new \ReflectionClass($className);

        return $class->newInstanceArgs($class->hasMethod('__construct') ? $args : []);
    }

    /**
     * @param $obj
     * @param $var
     * @param array $args
     * @return mixed|$this
     */
    static public function &fetchVar($obj, &$var, array $args = [])
    {
        if ($args) {
            $var = array_shift($args);

            return $obj;
        }

        return $var;
    }

    /**
     * @param $obj
     * @param $var
     * @param array $args
     * @param bool $unsigned
     * @return int|float|bool|string|$this
     */
    static public function &fetchScalarVar($obj, &$var, array $args = [], $unsigned = false)
    {
        if ($args) {
            $value = static::fetchScalar(array_shift($args), $unsigned);

            $addType = $args ? array_shift($args) : static::VAR_REPLACE;

            switch ($addType) {
                case static::VAR_APPEND:
                    $var .= $value;

                    break;
                case static::VAR_PREPEND:
                    $var = $value . $var;

                    break;
                case static::VAR_REPLACE:
                    $var = $value;

                    break;
            }

            return $obj;
        }

        $var = static::fetchScalar($var, $unsigned); return $var;
    }

    static public function fetchScalar($var)
    {
        if (!is_scalar($var)) {
            $var = (string)$var;
        }

        return $var;
    }

    /**
     * @param $obj
     * @param $var
     * @param array $args
     * @return string|$this
     */
    static public function &fetchStrVar($obj, &$var, array $args = [])
    {
        if ($args) {
            $value = (string)array_shift($args);

            $addType = $args ? array_shift($args) : static::VAR_REPLACE;

            switch ($addType) {
                case static::VAR_APPEND:
                    $var .= $value;

                    break;
                case static::VAR_PREPEND:
                    $var = $value . $var;

                    break;
                case static::VAR_REPLACE:
                    $var = $value;

                    break;
            }

            return $obj;
        }

        $var = (string)$var; return $var;
    }

    static public function &fetchBoolVar($obj, &$var, array $args = [])
    {
        if ($args) {
            $var = (bool)array_shift($args);

            return $obj;
        }

        $var = (bool)$var; return $var;
    }

    /**
     * @param $obj
     * @param $var
     * @param array $args
     * @param bool $unsigned
     * @return int|$this
     */
    static public function &fetchIntVar($obj, &$var, array $args = [], $unsigned = false)
    {
        if ($args) {
            $var = static::fetchInt(array_shift($args), $unsigned);

            return $obj;
        }

        $var = static::fetchInt($var, $unsigned); return $var;
    }

    static public function fetchInt($var, $unsigned = false)
    {
        $var = (int)$var;

        if ($unsigned and $var < 0) {
            $var = 0;
        }

        return $var;
    }

    /**
     * @param $obj
     * @param $var
     * @param array $args
     * @param bool $unsigned
     * @return float|$this
     */
    static public function &fetchFloatVar($obj, &$var, array $args = [], $unsigned = false)
    {
        if ($args) {
            $var = static::fetchFloat(array_shift($args), $unsigned);

            return $obj;
        }

        $var = static::fetchFloat($var, $unsigned); return $var;
    }

    static public function fetchFloat($var, $unsigned = false)
    {
        $var = (float)$var;

        if ($unsigned and $var < 0) {
            $var = 0;
        }

        return $var;
    }

    /**
     * @param $obj
     * @param $var
     * @param array $args
     * @param array $stack
     * @param null $default
     * @return mixed|$this
     */
    static public function &fetchEnumVar($obj, &$var, array $args = [], array $stack = [], $default = null)
    {
        if ($args) {
            $var = static::fetchEnum(array_shift($args), $stack, $default);

            return $obj;
        }

        $var = static::fetchEnum($var, $stack, $default); return $var;
    }

    static public function fetchEnum($var, array $stack = [], $default = null)
    {
        if (!in_array($var, $stack)) {
            $var = $default;
        }

        return $var;
    }

    /**
     * @param $obj
     * @param $var
     * @param array $args
     * @param callable $callback
     * @return mixed|$this
     */
    static public function &fetchCallbackVar($obj, &$var, array $args = [], Closure $callback = null)
    {
        if ($args) {
            $var = static::fetchCallback(array_shift($args), $callback);

            return $obj;
        }

        $var = static::fetchCallback($var, $callback); return $var;
    }

    static public function &fetchCallback($value, Closure $callback = null)
    {
        if (($callback instanceof Closure)) {
            $value = $callback($value);
        }

        return $value;
    }

    static public function &fetchArrayObjVar(InternalInterface $obj, &$var, array $args = [])
    {
        if (!($var instanceof ArrayObject)) {
            $var = ArrayObject::create($obj->appName());
        }

        if ($args) {
            $key = array_shift($args);

            if (is_array($key)) {
                $addType = $args ? array_shift($args) : static::VAR_APPEND;

                if ($addType === true) {
                    $addType = static::VAR_REPLACE;
                }

                if ($addType == static::VAR_REPLACE) {
                    $var->exchange($key);

                    return $obj;
                }

                $replace = (bool)array_shift($args);

                if ($replace) {
                    switch ($addType) {
                        case static::VAR_APPEND:
                            $var->selfReplace($key);

                            break;
                        case static::VAR_PREPEND:
                            $var->selfReplacePrepend($key);

                            break;
                    }
                } else {
                    switch ($addType) {
                        case static::VAR_APPEND:
                            $var->selfMerge($key);

                            break;
                        case static::VAR_PREPEND:
                            $var->selfMergePrepend($key);

                            break;
                    }
                }

                return $obj;
            }

            if ($args) {
                $var[$key] = array_shift($args);

                return $obj;
            }

            if (array_key_exists($key, $var)) {
                return $var[$key];
            }

            $return = null; return $return;
        }

        return $var;
    }

    /**
     * @param $obj
     * @param $var
     * @param array $args
     * @return mixed|$this
     */
    static public function &fetchArrayVar($obj, &$var, array $args = [])
    {
        $var = Arr::bring($var);

        if ($args) {
            $key = array_shift($args);

            if (is_array($key)) {
                $addType = $args ? array_shift($args) : static::VAR_APPEND;

                if ($addType === true) {
                    $addType = static::VAR_REPLACE;
                }

                if ($addType == static::VAR_REPLACE) {
                    $var = $key;

                    return $obj;
                }

                $replace = (bool)array_shift($args);

                $recursive = (bool)array_shift($args);

                if ($replace) {
                    if ($recursive) {
                        switch ($addType) {
                            case static::VAR_APPEND:
                                $var = array_replace_recursive($var, $key);

                                break;
                            case static::VAR_PREPEND:
                                $var = array_replace_recursive($key, $var);

                                break;
                        }
                    } else {
                        switch ($addType) {
                            case static::VAR_APPEND:
                                $var = array_replace($var, $key);

                                break;
                            case static::VAR_PREPEND:
                                $var = array_replace($key, $var);

                                break;
                        }
                    }
                } else {
                    if ($recursive) {
                        switch ($addType) {
                            case static::VAR_APPEND:
                                $var = array_merge_recursive($var, $key);

                                break;
                            case static::VAR_PREPEND:
                                $var = array_merge_recursive($key, $var);

                                break;
                        }
                    } else {
                        switch ($addType) {
                            case static::VAR_APPEND:
                                $var = array_merge($var, $key);

                                break;
                            case static::VAR_PREPEND:
                                $var = array_merge($key, $var);

                                break;
                        }
                    }
                }

                return $obj;
            }

            if ($args) {
                $var[$key] = array_shift($args);

                return $obj;
            }

            if (array_key_exists($key, $var)) {

                return $var[$key];
            }

            $return = null; return $return;
        }

        return $var;
    }
}