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

    protected $format = null;

    protected $callback = null;

    protected $compiled = null;

    protected $strict = true;

    protected $delimiter = '/';

    protected $params = [];

    protected $defaults = [];

    public function __construct($format, callable $callback = null)
    {
        $this->compileFormat($format);

        $this->callback($callback);

        return $this;
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

    protected function compile($format)
    {
        $curlyBrR = new InNamespace('{', '}', false);

        $squareBrR = new InNamespace('[', ']');

        $findRegex = "(?:{$curlyBrR}(\\?)?)|(?:{$squareBrR}(\\?)?)";

        $compiled = null;

        $params = [];

        $defaults = [];

        // find all "{param}?" and "[format]?"
        if (preg_match_all(Regex::pattern($findRegex), $format, $matches)) {
            $paramKey = 1;
            $paramRK = 2;

            $subFormatKey = 3;
            $subFormatRK = 4;

            // split remain string
            $parts = preg_split(Regex::pattern($findRegex), $format);

            foreach($parts as $key => $remain) {
                if ($remain) {
                    $compiled .= Regex::quote($remain);
                }

                if (array_key_exists($key, $matches[0])) {
                    if ($param = $matches[$paramKey][$key]) {
                        list($paramName, $paramDefault, $paramRegex) = $this->fetchParam($param);

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

    protected function fetchParam($param)
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

    public function dispatch($path)
    {
        if ($this->match($path, $param)) {
            $callback = $this->callback();

            if ($callback) {
                return $this->app()->binder()->call($callback, $param);
            }
        }

        return false;
    }

    public function match($path, &$params = [])
    {
        $pattern = '^' . $this->compiled() . ($this->strict() ? '' : '(.*)') . '$';

        if (preg_match(Regex::pattern($pattern), $path, $matches)) {
            array_shift($matches);

            $params = $this->defaults();

            foreach($this->params() as $key => $param) {
                if (array_key_exists($key, $matches)) {
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

            return true;
        }

        return false;
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
}