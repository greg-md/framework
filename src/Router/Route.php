<?php

namespace Greg\Router;

use Greg\Engine\InternalTrait;
use Greg\Regex\InNamespace;
use Greg\Storage\AccessorTrait;
use Greg\Tool\Arr;
use Greg\Tool\Obj;
use Greg\Tool\Regex;
use Greg\Tool\Str;
use Greg\Tool\Url;

class Route implements \ArrayAccess
{
    use AccessorTrait, RouterTrait, InternalTrait;

    const TYPE_GET = 'get';

    const TYPE_POST = 'post';

    const TYPE_GROUP = 'group';

    protected $name = null;

    protected $format = null;

    protected $type = null;

    protected $callback = null;

    protected $compiled = null;

    protected $strict = true;

    protected $encodeValues = true;

    protected $delimiter = '/';

    protected $regexMatchDelimiter = false;

    protected $params = [];

    protected $defaults = [];

    protected $lastMatchedPath = null;

    protected $parent = null;

    protected $router = null;

    protected $lastMatchedCleanParams = [];

    protected $onMatch = [];

    public function __construct($name, $format, $type = null, callable $callback = null)
    {
        $this->name($name);

        $this->compileFormat($format);

        $this->type($type);

        $this->callback($callback);

        return $this;
    }

    /**
     * @param string $name
     * @param string $format
     * @param null $type
     * @param callable|array $settings
     * @return Route
     */
    public function createRoute($name, $format, $type = null, $settings = null)
    {
        return $this->_createRoute($name, $format, $type, $settings)->parent($this);
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

            $greedy = false;

            $nameLen = mb_strlen($name);

            if ($name[$nameLen - 1] == '?') {
                $name = mb_substr($name, 0, $nameLen - 1);

                $greedy = true;
            }

            $default = Arr::get($matches, 2);

            $regex = Arr::hasRef($matches, 3) ? Regex::disableGroups($matches[3]) : null;

            if (!$regex) {
                $regex = ($this->regexMatchDelimiter() ? '.+' : '[^' . Regex::quote($this->delimiter()) . ']+');

                if ($greedy) {
                    $regex .= '?';
                }
            }
        }

        return [$name, $default, $regex];
    }

    public function dispatch(array $params = [])
    {
        try {
            if ($callback = $this->callback()) {
                return $this->callCallable($callback, $params + $this->lastMatchedParams(), $this);
            }
        } catch (\Exception $e) {
            return $this->dispatchException($e);
        }

        return null;
    }

    protected function dispatchException(\Exception $e)
    {
        if ($error = $this->onError()) {
            $route = $this->_createRoute('error', '', null, $error)->onError([], true);

            return $route->dispatch([
                'exception' => $e,
            ]);
        }

        throw $e;
    }

    public function match($path, array &$matchedParams = [])
    {
        $pattern = '^' . $this->compiled() . (($this->isGroup() or !$this->strict()) ? '(.*)' : '') . '$';

        $matchedRoute = false;

        if (preg_match(Regex::pattern($pattern), $path, $matches)) {
            array_shift($matches);

            if ($this->isGroup()) {
                $subPath = array_pop($matches);

                foreach($this->routes as $route) {
                    if ($subMatchedRoute = $route->match($subPath)) {
                        $matchedRoute = $subMatchedRoute;

                        break;
                    }
                }
            } else {
                $matchedRoute = $this;
            }

            if ($matchedRoute) {
                $params = [];

                foreach($this->params() as $key => $param) {
                    if (array_key_exists($key, $matches) and !Str::isEmpty($matches[$key])) {
                        $params[$param] = $this->decode($matches[$key]);
                    } else {
                        $params[$param] = $this->defaults($param);
                    }
                }

                if (!$this->isGroup() and !$this->strict()) {
                    $remain = array_pop($matches);

                    $remain = Str::splitPath($remain, $this->delimiter());

                    $params = array_merge($params, $this->chunkParams($remain));
                }

                $params += $this->defaults();

                $cleanParams = $params;

                $params = $this->bindRouteParams($params);

                $this->lastMatchedPath($path);

                $this->lastMatchedCleanParams($cleanParams, Obj::PROP_REPLACE);

                $this->lastMatchedParams($params, Obj::PROP_REPLACE);

                if ($matchedRoute !== $this) {
                    $matchedRoute->lastMatchedPath($path);

                    $matchedRoute->lastMatchedCleanParams($cleanParams, Obj::PROP_PREPEND);

                    $matchedRoute->lastMatchedParams($params, Obj::PROP_PREPEND);
                }

                $matchedParams = $params;

                foreach($this->onMatch as $callable) {
                    $this->callCallableWith($callable, $matchedRoute);
                }
            }
        }

        return $matchedRoute;
    }

    public function onMatch(callable $callable)
    {
        $this->onMatch[] = $callable;

        return $this;
    }

    public function bindRouteParams(array $params)
    {
        $params = $this->bindParams($params);

        if ($router = $this->router()) {
            $params = $router->bindParams($params);
        }

        return $params;
    }

    public function isGet()
    {
        return $this->type() == static::TYPE_GET;
    }

    public function isPost()
    {
        return $this->type() == static::TYPE_POST;
    }

    public function isGroup()
    {
        return $this->type() == static::TYPE_GROUP;
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

    protected function cleanParams(array $params = [])
    {
        $params = array_diff_assoc($params, $this->defaults());

        $params = array_filter($params);

        return $params;
    }

    public function fetchPath(array &$params = [])
    {
        $params = $this->cleanParams($params);

        $compiled = $this->fetchFormat($this->format(), $params);

        if ($parent = $this->parent()) {
            $params += $parent->lastMatchedParams();

            $compiled = $parent->fetchPath($params) . ($compiled !== '/' ? $compiled : null);
        }

        return $compiled;
    }

    public function fetch(array $params = [], $full = false)
    {
        $compiled = $this->fetchPath($params);

        if (!$compiled) {
            $compiled = '/';
        }

        if ($params) {
            if ($this->strict()) {
                $compiled .= '?' . http_build_query($params);
            } else {
                $delimiter = $this->delimiter();

                // Need to make double encoding and then decode values
                $params = Arr::each($params, function($value, $key) {
                    return [$this->encode($value), $this->encode($key)];
                });

                $compiled .= ($compiled !== $delimiter ? $delimiter : '') . implode($delimiter, Arr::pack($params, $delimiter));
            }
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

                        if ($paramDefault != $value) {
                            $usedParams[] = $paramName;
                        }
                    } elseif ($subFormat = $matches[$subFormatKey][$key]) {
                        $subFormatRequired = !$matches[$subFormatRK][$key];

                        $oldParams = $params;

                        $subCompiled = $this->fetchFormat($subFormat, $params, $subFormatRequired);

                        if (!Str::isEmpty($subCompiled)) {
                            if ($subFormatRequired or $compiled or $oldParams !== $params) {
                                $compiled[] = $subCompiled;
                            }
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

    public function regexMatchDelimiter($value = null)
    {
        return Obj::fetchBoolVar($this, $this->{__FUNCTION__}, ...func_get_args());
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

    public function lastMatchedCleanParams($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function parent(Route $value = null)
    {
        return Obj::fetchVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function router(Router $value = null)
    {
        return Obj::fetchVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    /* Magic methods for ArrayAccess interface */

    public function offsetExists($key)
    {
        return Arr::hasRef($this->accessor(), $key);
    }

    public function offsetSet($key, $value)
    {
        Arr::set($this->accessor(), $key, $value);

        return $this;
    }

    public function &offsetGet($key)
    {
        return $this->accessor()[$key];
    }

    public function offsetUnset($key)
    {
        Arr::del($this->accessor(), $key);

        return $this;
    }
}