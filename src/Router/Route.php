<?php

namespace Greg\Router;

use Greg\Support\Engine\Internal;
use Greg\Support\Storage\Accessor;
use Greg\Support\Storage\ArrayAccess;
use Greg\Support\Arr;
use Greg\Support\Obj;
use Greg\Support\Regex;
use Greg\Support\Regex\InNamespace;
use Greg\Support\Str;
use Greg\Support\Url;

class Route implements \ArrayAccess
{
    use Accessor, ArrayAccess, Internal;

    protected $name = null;

    protected $format = null;

    protected $type = null;

    protected $callback = null;

    protected $action = null;

    protected $compiled = null;

    protected $strict = true;

    protected $encodeValues = true;

    protected $delimiter = '/';

    protected $params = [];

    protected $defaults = [];

    protected $lastMatchedPath = [];

    public function __construct($name, $format, $type = null, callable $callback = null)
    {
        $this->name($name);

        $this->compileFormat($format);

        $this->type($type);

        $this->callback($callback);

        return $this;
    }

    static public function create($appName, $name, $format, $type = null, callable $callback = null)
    {
        return static::newInstanceRef($appName, $name, $format, $type, $callback);
    }

    public function compileFormat($format)
    {
        $this->format($format);

        list($compiled, $params, $defaults) = $this->compile($format);

        $this->compiled($compiled);

        $this->params($params);

        $this->defaults($defaults);

        return $this;
    }

    protected function getRegexPattern()
    {
        $curlyBrR = new InNamespace('{', '}', false);

        $squareBrR = new InNamespace('[', ']');

        $findRegex = "(?:{$curlyBrR}(\\?)?)|(?:{$squareBrR}(\\?)?)";

        $pattern = Regex::pattern($findRegex);

        return $pattern;
    }

    protected function compile($format)
    {
        $compiled = null;

        $params = [];

        $defaults = [];

        $pattern = $this->getRegexPattern();

        // find all "{param}?" and "[format]?"
        if (preg_match_all($pattern, $format, $matches)) {
            $paramKey = 1;
            $paramRK = 2;

            $subFormatKey = 3;
            $subFormatRK = 4;

            // split remain string
            $parts = preg_split($pattern, $format);

            foreach($parts as $key => $remain) {
                if ($remain) {
                    $compiled .= Regex::quote($remain);
                }

                if (array_key_exists($key, $matches[0])) {
                    if ($param = $matches[$paramKey][$key]) {
                        list($paramName, $paramDefault, $paramRegex) = $this->splitParam($param);

                        $params[] = $paramName;

                        if ($paramDefault) {
                            $defaults[$paramName] = $paramDefault;
                        }

                        $paramRegex = $paramRegex ?: '.+';

                        $compiled .= "({$paramRegex})" . $matches[$paramRK][$key];
                    } elseif ($subFormat = $matches[$subFormatKey][$key]) {
                        list($subCompiled, $subParams, $subDefaults) = $this->compile($subFormat);

                        $compiled .= "(?:{$subCompiled})" . $matches[$subFormatRK][$key];

                        $params = array_merge($params, $subParams);

                        $defaults = array_merge($defaults, $subDefaults);
                    }
                }
            }
        } else {
            $compiled = Regex::quote($format);
        }

        return [$compiled, $params, $defaults];
    }

    protected function splitParam($param)
    {
        $name = $param;

        $default = $regex = null;

        // extract from var:default|regex
        if (preg_match(Regex::pattern('^((?:\\\:|\\\||[^\:])+?)(?:\:((?:|\\\||[^\|])+?))?(?:\|(.+?))?$'), $param, $matches)) {
            $name = $matches[1];

            $default = Arr::get($matches, 2);

            $regex = Arr::has($matches, 3) ? Regex::disableGroups($matches[3]) : null;
        }

        return [$name, $default, $regex];
    }

    public function dispatch(array $params = [], $catchException = true)
    {
        if ($callback = $this->callback()) {
            return $this->app()->binder()->call($callback, $this);
        }

        if ($action = $this->action()) {
            list($controller, $action) = explode('@', $action);

            $controller = Str::spinalCase($controller);

            $action = Str::spinalCase($action);

            $params = [
                    'controller' => $controller,
                    'action' => $action,
                ] + $this->lastMatchedParams() + $params;

            return $this->app()->action($action, $controller, $params, $catchException);
        }

        return null;
    }

    public function match($path, array &$params = [])
    {
        $pattern = '^' . $this->compiled() . ($this->strict() ? '' : '(.*)') . '$';

        $matched = false;

        if (preg_match(Regex::pattern($pattern), $path, $matches)) {
            array_shift($matches);

            $params = $this->defaults();

            foreach($this->params() as $key => $param) {
                if (array_key_exists($key, $matches) and !Str::isEmpty($matches[$key])) {
                    $params[$param] = $this->decode($matches[$key]);
                } else {
                    $params[$param] = $this->defaults($param);
                }
            }

            if (!$this->strict()) {
                $remain = array_pop($matches);

                $remain = Str::splitPath($remain, $this->delimiter());

                $params = array_merge($params, $this->chunkParams($remain));
            }

            $this->lastMatchedPath($path);

            $this->lastMatchedParams($params, Obj::PROP_REPLACE);

            $matched = true;
        }

        return $matched;
    }

    public function chunkParams($params)
    {
        $params = array_chunk($params, 2);

        $return = [];

        foreach($params as $param) {
            $return[$this->decode($param[0])] = $this->decode(Arr::get($param, 1));
        }

        return $return;
    }

    public function fetch(array $params = [], $full = false)
    {
        $params = array_diff_assoc($params, $this->defaults());

        $params = array_filter($params);

        $compiled = $this->fetchFormat($this->format(), $params);

        if ($params) {
            if ($this->strict()) {
                $compiled .= '?' . http_build_query($params);
            } else {
                $delimiter = $this->delimiter();

                // Need to make double encoding and then decode values
                $params = Arr::each($params, function($value, $key) {
                    return [$this->encode($value), $this->encode($key)];
                });

                $compiled .= $delimiter . implode($delimiter, Arr::pack($params, $delimiter));
            }
        }

        if (!$compiled) {
            $compiled = '/';
        }

        if ($full) {
            $compiled = Url::full($compiled);
        }

        return $compiled;
    }

    protected function encode($value)
    {
        return $this->encodeValues() ? urlencode($value) : $value;
    }

    protected function decode($value)
    {
        return $this->encodeValues() ? urldecode($value) : $value;
    }

    protected function fetchFormat($format, &$params = [], $required = true)
    {
        $pattern = $this->getRegexPattern();

        $usedParams = [];

        // find all "{param}?" and "[format]?"
        if (preg_match_all($pattern, $format, $matches)) {
            $paramKey = 1;
            $paramRK = 2;

            $subFormatKey = 3;
            $subFormatRK = 4;

            // split remain string
            $parts = preg_split($pattern, $format);

            // start from the last
            $parts = array_reverse($parts, true);

            $compiled = [];

            foreach($parts as $key => $remain) {
                if (array_key_exists($key, $matches[0])) {
                    if ($param = $matches[$paramKey][$key]) {
                        list($paramName, $paramDefault) = $this->splitParam($param);

                        $paramRequired = !$matches[$paramRK][$key];

                        $value = Arr::get($params, $paramName, $paramDefault);

                        if ($paramRequired) {
                            if (Str::isEmpty($value)) {
                                if (!$required) {
                                    return null;
                                }
                                throw new \Exception('Param `' . $paramName . '` is required in route `' . $this->name() . '`.');
                            }

                            $compiled[] = $this->encode($value);
                        } else {
                            if ((!Str::isEmpty($value) and $value != $paramDefault) or $compiled) {
                                $compiled[] = $this->encode($value);
                            }
                        }

                        if ($value != $paramDefault) {
                            $usedParams[] = $paramName;
                        }

                    } elseif ($subFormat = $matches[$subFormatKey][$key]) {
                        $subCompiled = $this->fetchFormat($subFormat, $params, !$matches[$subFormatRK][$key]);
                        if (!Str::isEmpty($subCompiled)) {
                            $compiled[] = $subCompiled;
                        }
                    }
                }

                if (!Str::isEmpty($remain)) {
                    $compiled[] = $remain;
                }
            }

            $compiled = array_reverse($compiled);

            $compiled = implode('', $compiled);
        } else {
            $compiled = $format;
        }

        if (!$required and !$usedParams) {
            return null;
        }

        $usedParams and Arr::del($params, ...$usedParams);

        return $compiled;
    }

    public function name($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function format($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function callback(callable $value = null)
    {
        return Obj::fetchVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function action($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function type($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function compiled($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function strict($value = null)
    {
        return Obj::fetchBoolVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function encodeValues($value = null)
    {
        return Obj::fetchBoolVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function delimiter($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function params($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function defaults($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function lastMatchedPath($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function lastMatchedParams($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->storage, ...func_get_args());
    }
}