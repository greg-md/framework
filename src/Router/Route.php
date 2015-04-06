<?php

namespace Greg\Router;

use Greg\Engine\Internal;
use Greg\Support\Arr;
use Greg\Support\Obj;
use Greg\Support\Regex;
use Greg\Support\Regex\InNamespace;
use Greg\Support\Str;

class Route
{
    use Internal;

    protected $name = null;

    protected $format = null;

    protected $callback = null;

    protected $compiled = null;

    protected $strict = true;

    protected $delimiter = '/';

    protected $params = [];

    protected $defaults = [];

    protected $lastMatchedPath = [];

    protected $lastMatchedParams = [];

    public function __construct($name, $format, callable $callback = null)
    {
        $this->name($name);

        $this->compileFormat($format);

        $this->callback($callback);

        return $this;
    }

    static public function create($appName, $name, $format, callable $callback = null)
    {
        return static::newInstanceRef($appName, $name, $format, $callback);
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

            $default = $matches[2];

            $regex = $matches[3] ? Regex::disableGroups($matches[3]) : null;
        }

        return [$name, $default, $regex];
    }

    public function dispatch(array $params = [])
    {
        $callback = $this->callback();

        $params += $this->lastMatchedParams();

        if ($callback) {
            return $this->app()->binder()->call($callback, $params);
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
                    $params[$param] = $matches[$key];
                }
            }

            if (!$this->strict()) {
                $remain = array_pop($matches);

                $remain = Str::splitPath($remain, $this->delimiter());

                $remain = array_chunk($remain, 2);

                foreach($remain as $param) {
                    $params[$param[0]] = Arr::get($param, 1);
                }
            }

            $this->lastMatchedPath($path);

            $this->lastMatchedParams($params, Obj::PROP_REPLACE);

            $matched = true;
        }

        return $matched;
    }

    public function fetch(array $params = [])
    {
        //$path = $this->fetchFormat($this->format(), $params);

        $params = array_diff_assoc($params, $this->defaults());

        $compiled = $this->fetchFormat($this->format(), $params);

        return $compiled;
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
                        list($paramName, $paramDefault, $paramRegex) = $this->splitParam($param);

                        $paramRequired = !$matches[$paramRK][$key];

                        $value = Arr::get($params, $paramName, $paramDefault);

                        if ($paramRequired) {
                            if (Str::isEmpty($value)) {
                                if (!$required) {
                                    return null;
                                }
                                throw new Exception('Param `' . $paramName . '` is required in route `' . $this->name() . '`.');
                            }

                            $compiled[] = $value;
                        } else {
                            if ((!Str::isEmpty($value) and $value != $paramDefault) or $compiled) {
                                $compiled[] = $value;
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

    public function compiled($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function strict($value = null)
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
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }
}