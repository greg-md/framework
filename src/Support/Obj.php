<?php

namespace Greg\Support;

use Greg\Storage\ArrayObject;

class Obj
{
    const PROP_APPEND = 'append';

    const PROP_PREPEND = 'prepend';

    const PROP_REPLACE = 'replace';

    static public function newInstance($className, ...$args)
    {
        return static::newInstanceArgs($className, $args);
    }

    /**
     * @param $className
     * @param array $args
     * @return object
     */
    static public function newInstanceArgs($className, array $args = [])
    {
        $class = new \ReflectionClass($className);

        $self = $class->newInstanceWithoutConstructor();

        if (method_exists($self, '__bind')) {
            $self->__bind();
        }

        if ($constructor = $class->getConstructor()) {
            $constructor->invokeArgs($self, $args);
        }

        return $self;
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

            $addType = $args ? array_shift($args) : static::PROP_REPLACE;

            switch ($addType) {
                case static::PROP_APPEND:
                    $var .= $value;

                    break;
                case static::PROP_PREPEND:
                    $var = $value . $var;

                    break;
                case static::PROP_REPLACE:
                    $var = $value;

                    break;
            }

            return $obj;
        }

        $var = static::fetchScalar($var, $unsigned); return $var;
    }

    static public function fetchScalar($var)
    {
        return is_scalar($var) ? $var : (string)$var;
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

            $addType = $args ? array_shift($args) : static::PROP_REPLACE;

            switch ($addType) {
                case static::PROP_APPEND:
                    $var .= $value;

                    break;
                case static::PROP_PREPEND:
                    $var = $value . $var;

                    break;
                case static::PROP_REPLACE:
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
        return (int)(($unsigned and $var < 0) ? 0 : $var);
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
        return (float)(($unsigned and $var < 0) ? 0 : $var);
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
        return in_array($var, $stack) ? $var : $default;
    }

    /**
     * @param $obj
     * @param $var
     * @param array $args
     * @param callable $callable
     * @return mixed|$this
     */
    static public function &fetchCallbackVar($obj, &$var, array $args = [], callable $callable = null)
    {
        if ($args) {
            $var = static::fetchCallback(array_shift($args), $callable);

            return $obj;
        }

        $var = static::fetchCallback($var, $callable); return $var;
    }

    static public function &fetchCallback($value, callable $callable = null)
    {
        return $callable ? call_user_func_array($callable, [$value]) : $value;
    }

    static public function &fetchArrayObjVar($obj, &$var, array $args = [])
    {
        if (!($var instanceof ArrayObject)) {
            $var = new ArrayObject($var);
        }

        if ($args) {
            $key = array_shift($args);

            if (is_array($key)) {
                $addType = $args ? array_shift($args) : static::PROP_APPEND;

                if ($addType === true) {
                    $addType = static::PROP_REPLACE;
                }

                if ($addType == static::PROP_REPLACE) {
                    $var->exchange($key);

                    return $obj;
                }

                $replace = (bool)array_shift($args);

                if ($replace) {
                    switch ($addType) {
                        case static::PROP_APPEND:
                            $var->replaceMe($key);

                            break;
                        case static::PROP_PREPEND:
                            $var->replacePrependMe($key);

                            break;
                    }
                } else {
                    switch ($addType) {
                        case static::PROP_APPEND:
                            $var->mergeMe($key);

                            break;
                        case static::PROP_PREPEND:
                            $var->mergePrependMe($key);

                            break;
                    }
                }

                return $obj;
            }

            if ($args) {
                $var[$key] = array_shift($args);

                return $obj;
            }

            return $var->get($key);
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
                $addType = $args ? array_shift($args) : static::PROP_APPEND;

                if ($addType === true) {
                    $addType = static::PROP_REPLACE;
                }

                if ($addType == static::PROP_REPLACE) {
                    $var = $key;

                    return $obj;
                }

                $replace = (bool)array_shift($args);

                $recursive = (bool)array_shift($args);

                if ($replace) {
                    if ($recursive) {
                        switch ($addType) {
                            case static::PROP_APPEND:
                                $var = array_replace_recursive($var, $key);

                                break;
                            case static::PROP_PREPEND:
                                $var = array_replace_recursive($key, $var);

                                break;
                        }
                    } else {
                        switch ($addType) {
                            case static::PROP_APPEND:
                                $var = array_replace($var, $key);

                                break;
                            case static::PROP_PREPEND:
                                $var = array_replace($key, $var);

                                break;
                        }
                    }
                } else {
                    if ($recursive) {
                        switch ($addType) {
                            case static::PROP_APPEND:
                                $var = array_merge_recursive($var, $key);

                                break;
                            case static::PROP_PREPEND:
                                $var = array_merge_recursive($key, $var);

                                break;
                        }
                    } else {
                        switch ($addType) {
                            case static::PROP_APPEND:
                                $var = array_merge($var, $key);

                                break;
                            case static::PROP_PREPEND:
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