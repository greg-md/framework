<?php

namespace Greg\Support\Tool;

use Greg\Support\Storage\ArrayObject;
use Greg\Support\Storage\ArrayReference;

class Obj
{
    const PROP_APPEND = 'append';

    const PROP_PREPEND = 'prepend';

    const PROP_REPLACE = 'replace';

    static public function loadInstance($className, ...$args)
    {
        return static::loadInstanceArgs($className, $args);
    }

    /**
     * @param $className
     * @param array $args
     * @return object
     */
    static public function loadInstanceArgs($className, array $args = [])
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

    static public function expectedArgs(callable $callable)
    {
        if (Str::isScalar($callable) and strpos($callable, '::')) {
            $callable = explode('::', $callable, 2);
        }

        if (is_array($callable)) {
            return (new \ReflectionMethod($callable[0], $callable[1]))->getParameters();
        }

        return (new \ReflectionFunction($callable))->getParameters();
    }

    static public function fetchRef($value)
    {
        return ($value instanceof ArrayReference) ? $value->get() : $value;
    }

    static public function callWith(callable $callable, ...$args)
    {
        return static::callWithRef($callable, ...$args);
    }

    static public function callWithRef(callable $callable, &...$args)
    {
        return static::callWithArgs($callable, $args);
    }

    static public function callWithArgs(callable $callable, array $args = [])
    {
        $funcArgs = [];

        if ($expectedArgs = static::expectedArgs($callable)) {
            $funcArgs = static::fetchExpectedArgs($expectedArgs, $args);
        }

        return call_user_func_array($callable, $funcArgs);
    }

    static public function fetchExpectedArgs(array $expectedArgs, array $customArgs = [], callable $expectedCallback = null)
    {
        $assocArgs = [];

        foreach($customArgs as $key => $value) {
            if (is_int($key)) {
                $assocArgs[get_class($value)] = $value;
            } else {
                $assocArgs[$key] = $value;
            }
        }

        /* @var $expectedArgs \ReflectionParameter[] */
        $expectedArgs = array_reverse($expectedArgs);

        $returnArgs = [];

        foreach ($expectedArgs as $expectedArg) {
            if (!$returnArgs and !$expectedArg->getClass() and $expectedArg->isOptional()) {
                continue;
            }

            if ($assocArgs and $expectedType = $expectedArg->getClass() and Arr::has($assocArgs, $expectedType->getName())) {
                $returnArgs[] = $assocArgs[$expectedType->getName()];
            } elseif (is_callable($expectedCallback)) {
                $returnArgs[] = call_user_func_array($expectedCallback, [$expectedArg]);
            } else {
                $returnArgs[] = static::expectedArg($expectedArg);
            }
        }

        $returnArgs = array_reverse($returnArgs);

        return $returnArgs;
    }

    static public function expectedArg(\ReflectionParameter $expectedArg)
    {
        if (!$expectedArg->isOptional()) {
            throw new \Exception('Argument `' . $expectedArg->getName() . '` is required in `'
                . $expectedArg->getDeclaringClass() . '::' . $expectedArg->getDeclaringFunction() . '`');
        }

        $arg = $expectedArg->getDefaultValue();

        return $arg;
    }

    /**
     * @param $return
     * @param $var
     * @param $value
     * @return mixed|$this
     */
    static public function &fetchVar($return, &$var, $value = null)
    {
        if (func_num_args() > 2) {
            $var = $value;

            return $return;
        }

        return $var;
    }

    /**
     * @param $return
     * @param $var
     * @param null $value
     * @param string $type
     * @return int|float|bool|string|$this
     */
    static public function &fetchScalarVar($return, &$var, $value = null, $type = self::PROP_REPLACE)
    {
        if (func_num_args() > 2) {
            switch ($type) {
                case static::PROP_APPEND:
                    $var .= $value;

                    break;
                case static::PROP_PREPEND:
                    $var = $value . $var;

                    break;
                case static::PROP_REPLACE:
                    $var = Str::isScalar($value) ? $value : (string)$value;

                    break;
            }

            return $return;
        }

        $var = Str::isScalar($var) ? $var : (string)$var;

        return $var;
    }

    /**
     * @param $return
     * @param $var
     * @param $value
     * @param string $type
     * @return string|$this
     */
    static public function &fetchStrVar($return, &$var, $value = null, $type = self::PROP_REPLACE)
    {
        if (func_num_args() > 2) {
            switch ($type) {
                case static::PROP_APPEND:
                    $var .= $value;

                    break;
                case static::PROP_PREPEND:
                    $var = $value . $var;

                    break;
                case static::PROP_REPLACE:
                    $var = (string)$value;

                    break;
            }

            return $return;
        }

        $var = (string)$var;

        return $var;
    }

    static public function &fetchBoolVar($return, &$var, $value = null)
    {
        if (func_num_args() > 2) {
            $var = (bool)$value;

            return $return;
        }

        $var = (bool)$var;

        return $var;
    }

    /**
     * @param $return
     * @param $var
     * @param bool $unsigned
     * @param $value
     * @return int|$this
     */
    static public function &fetchIntVar($return, &$var, $unsigned = false, $value = null)
    {
        if (func_num_args() > 3) {
            $var = static::fetchInt($value, $unsigned);

            return $return;
        }

        $var = static::fetchInt($var, $unsigned);

        return $var;
    }

    static public function fetchInt($var, $unsigned = false)
    {
        return (int)(($unsigned and $var < 0) ? 0 : $var);
    }

    /**
     * @param $return
     * @param $var
     * @param bool $unsigned
     * @param $value
     * @return float|$this
     */
    static public function &fetchFloatVar($return, &$var, $unsigned = false, $value = null)
    {
        if (func_num_args() > 3) {
            $var = static::fetchFloat($value, $unsigned);

            return $return;
        }

        $var = static::fetchFloat($var, $unsigned);

        return $var;
    }

    static public function fetchFloat($var, $unsigned = false)
    {
        return (float)(($unsigned and $var < 0) ? 0 : $var);
    }

    /**
     * @param $return
     * @param $var
     * @param array $stack
     * @param $value
     * @param null $default
     * @return mixed|$this
     */
    static public function &fetchEnumVar($return, &$var, array $stack, $default = null, $value = null)
    {
        if (func_num_args() > 4) {
            $var = static::fetchEnum($value, $stack, $default);

            return $return;
        }

        $var = static::fetchEnum($var, $stack, $default);

        return $var;
    }

    static public function fetchEnum($var, array $stack, $default = null)
    {
        return in_array($var, $stack) ? $var : $default;
    }

    /**
     * @param $return
     * @param $var
     * @param callable $callable
     * @param $value
     * @return mixed|$this
     */
    static public function &fetchCallableVar($return, &$var, callable $callable, $value = null)
    {
        if (func_num_args() > 3) {
            $var = call_user_func_array($callable, [$value]);

            return $return;
        }

        $var = call_user_func_array($callable, [$var]);

        return $var;
    }

    /**
     * @param $return
     * @param $var
     * @param callable $callable
     * @param $value
     * @return mixed|$this
     */
    static public function &fetchEmptyVar($return, &$var, callable $callable, $value = null)
    {
        if (func_num_args() > 3) {
            $var = $value;

            return $return;
        }

        if (!$var) {
            $var = call_user_func_array($callable, []);
        }

        return $var;
    }

    /**
     * @param $return
     * @param $var
     * @param null $key
     * @param null $value
     * @param string $type
     * @param bool $replace
     * @param bool $recursive
     * @return mixed|$this
     */
    static public function &fetchArrayObjVar($return, &$var, $key = null, $value = null, $type = self::PROP_APPEND, $replace = false, $recursive = false)
    {
        if (!($var instanceof ArrayObject)) {
            $var = new ArrayObject($var);
        }

        if (($num = func_num_args()) > 2) {
            if (is_array($key)) {
                $recursive = $replace;
                $replace = $type;
                $type = $value;

                if ($type === true) {
                    $type = static::PROP_REPLACE;
                }

                switch($type) {
                    case static::PROP_REPLACE:
                        $var->exchange($key);

                        break;
                    case static::PROP_APPEND:
                        if ($replace) {
                            if ($recursive) {
                                $var->replaceRecursiveMe($key);
                            } else {
                                $var->replaceMe($key);
                            }
                        } else {
                            if ($recursive) {
                                $var->mergeRecursiveMe($key);
                            } else {
                                $var->mergeMe($key);
                            }
                        }
                        break;
                    case static::PROP_PREPEND:
                        if ($replace) {
                            if ($recursive) {
                                $var->replacePrependRecursiveMe($key);
                            } else {
                                $var->replacePrependMe($key);
                            }
                        } else {
                            if ($recursive) {
                                $var->mergePrependRecursiveMe($key);
                            } else {
                                $var->mergePrependMe($key);
                            }
                        }
                        break;
                }

                return $return;
            }

            unset($recursive);

            if ($num > 3) {
                switch($type) {
                    case static::PROP_REPLACE:
                        $var->set($key, $value);

                        break;
                    case static::PROP_PREPEND:
                        $var->prependKey($key, $value);

                        break;
                    case static::PROP_APPEND:
                        $var->appendKey($key, $value);

                        break;
                }

                return $return;
            }

            if ($var->has($key)) {
                return $var[$key];
            }

            $return = null;

            return $return;
        }

        return $var;
    }

    /**
     * @param $return
     * @param $var
     * @param null $key
     * @param null $value
     * @param string $type
     * @param bool $recursive
     * @return mixed|$this
     */
    static public function &fetchArrayReplaceVar($return, &$var, $key = null, $value = null, $type = self::PROP_APPEND, $recursive = false)
    {
        if (($num = func_num_args()) > 2) {
            if (is_array($key)) {
                $value = null;

                if ($num > 3) {
                    $type = func_get_arg(3);
                }

                if ($num > 4) {
                    $recursive = func_get_arg(4);
                }

                return static::fetchArrayVarArrayKey($return, $var, $key, $type, true, $recursive);
            }

            if ($num > 3) {
                $type = $num > 4 ? func_get_arg(4) : static::PROP_REPLACE;

                return static::fetchArrayVarKeyValue($return, $var, $key, $value, $type);
            }

            return static::fetchArrayVarGetKey($var, $key);
        }

        Arr::bringRef($var);

        return $var;
    }

    /**
     * @param $return
     * @param $var
     * @param null $key
     * @param null $value
     * @param string $type
     * @param bool $replace
     * @param bool $recursive
     * @return mixed|$this
     */
    static public function &fetchArrayVar($return, &$var, $key = null, $value = null, $type = self::PROP_APPEND, $replace = false, $recursive = false)
    {
        if (($num = func_num_args()) > 2) {
            if (is_array($key)) {
                $value = null;

                if ($num > 3) {
                    $type = func_get_arg(3);
                }

                if ($num > 4) {
                    $replace = func_get_arg(4);
                }

                if ($num > 5) {
                    $recursive = func_get_arg(5);
                }

                return static::fetchArrayVarArrayKey($return, $var, $key, $type, $replace, $recursive);
            }

            if ($num > 3) {

                $type = $num > 4 ? func_get_arg(4) : static::PROP_REPLACE;

                return static::fetchArrayVarKeyValue($return, $var, $key, $value, $type);
            }

            return static::fetchArrayVarGetKey($var, $key);
        }

        Arr::bringRef($var);

        return $var;
    }

    static public function &fetchArrayVarIndex($return, &$var, $index = null, $value = null, $type = self::PROP_REPLACE, $delimiter = Arr::INDEX_DELIMITER)
    {
        if (($num = func_num_args()) > 2) {
            if (is_array($index)) {
                $value = null;

                if ($num > 3) {
                    $type = func_get_arg(3);
                }

                foreach(($indexes = $index) as $index => $value) {
                    static::fetchArrayVarIndexValue($return, $var, $index, $value, $type, $delimiter);
                }

                return $return;
            }

            if ($num > 3) {
                return static::fetchArrayVarIndexValue($return, $var, $index, $value, $type, $delimiter);
            }

            return static::fetchArrayVarGetIndex($var, $index, $delimiter);
        }

        Arr::bringRef($var);

        return $var;
    }

    static public function &fetchArrayVarGetKey(&$var, $key)
    {
        Arr::bringRef($var);

        if (array_key_exists($key, $var)) {
            return $var[$key];
        }

        $return = null;

        return $return;
    }

    static public function &fetchArrayVarGetIndex(&$var, $index, $delimiter = Arr::INDEX_DELIMITER)
    {
        Arr::bringRef($var);

        return Arr::getIndexRef($var, $index, null, $delimiter);
    }

    static public function &fetchArrayVarKeyValue($return, &$var, $key, $value, $type = self::PROP_APPEND)
    {
        Arr::bringRef($var);

        switch($type) {
            case static::PROP_REPLACE:
                $var[$key] = $value;

                break;
            case static::PROP_PREPEND:
                Arr::prependKey($var, $key, $value);

                break;
            case static::PROP_APPEND:
                Arr::appendKey($var, $key, $value);

                break;
        }

        return $return;
    }

    static public function &fetchArrayVarIndexValue($return, &$var, $index, $value, $type = self::PROP_APPEND, $delimiter = Arr::INDEX_DELIMITER)
    {
        Arr::bringRef($var);

        switch($type) {
            case static::PROP_REPLACE:
                Arr::setIndex($var, $index, $value, $delimiter);

                break;
            case static::PROP_PREPEND:
                Arr::prependIndex($var, $index, $value, $delimiter);

                break;
            case static::PROP_APPEND:
                Arr::appendIndex($var, $index, $value, $delimiter);

                break;
        }

        return $return;
    }

    static public function &fetchArrayVarArrayKey($return, &$var, array $key, $type = self::PROP_APPEND, $replace = false, $recursive = false)
    {
        Arr::bringRef($var);

        if ($type === null) {
            $type = static::PROP_APPEND;
        }

        if ($type === true) {
            $type = static::PROP_REPLACE;
        }

        switch($type) {
            case static::PROP_REPLACE:
                $var = $key;

                break;
            case static::PROP_APPEND:
                if ($replace) {
                    if ($recursive) {
                        $var = array_replace_recursive($var, $key);
                    } else {
                        $var = array_replace($var, $key);
                    }
                } else {
                    if ($recursive) {
                        $var = array_merge_recursive($var, $key);
                    } else {
                        $var = array_merge($var, $key);
                    }
                }
                break;
            case static::PROP_PREPEND:
                if ($replace) {
                    if ($recursive) {
                        $var = array_replace_recursive($key, $var);
                    } else {
                        $var = array_replace($key, $var);
                    }
                } else {
                    if ($recursive) {
                        $var = array_merge_recursive($key, $var);
                    } else {
                        $var = array_merge($key, $var);
                    }
                }
                break;
        }

        return $return;
    }

    /**
     * @param $name
     * @param array $prefixes
     * @param null $namePrefix
     * @return bool|string
     */
    static public function classExists($name, array $prefixes = [], $namePrefix = null)
    {
        $name = array_map(function($name) {
            return Str::phpName($name);
        }, Arr::bring($name));

        $name = implode('\\', $name);

        foreach($prefixes as $prefix) {
            $class = $prefix . $namePrefix . $name;

            if (class_exists($class)) {
                return $class;
            }
        }

        return false;
    }
}