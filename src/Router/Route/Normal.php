<?php

namespace Greg\Router\Route;

use Greg\Router\Route;
use Greg\Support\Obj;

class Normal extends Route
{
    protected $strict = true;

    public function setOption($key, $value = null)
    {
        switch($key) {
            case 'strict':
                $this->$key($value);

                break;
            default:
                parent::setOption($key, $value);
        }

        return $this;
    }

    public function fetch($path)
    {
        $path = $this->getPathParts($path);

        $format = $this->getPathParts($this->format());

        $strict = $this->strict();

        $param = $this->param();

        $index = 0;

        foreach($format as $key) {
            if ($key[0] == ':') {
                $key = substr($key, 1);

                if (array_key_exists($index, $path)) {
                    $param[$key] = $path[$index];
                } else {
                    if ($strict) {
                        return false;
                    }

                    break;
                }
            } else {
                if ($key !== $path[$index]) {
                    return false;
                }
            }

            ++$index;
        }

        $remain = array_slice($path, $index);

        if ($remain and !$this->extend()) {

            return false;
        }

        $param = array_replace($param, $this->partsToParam($remain));

        return $param;
    }

    protected function getFormatParam($allParam)
    {
        $format = $this->getPathParts($this->format());

        $strict = $this->strict();

        $param = [];

        // Detect format params from all params
        foreach($format as $key) {
            if ($key[0] == ':') {
                $formatKey = substr($key, 1);

                if (array_key_exists($formatKey, $allParam)) {
                    $param[$formatKey] = $allParam[$formatKey];
                } else {
                    if ($strict) {
                        $this->paramException($formatKey);
                    }

                    break;
                }
            }
        }

        return $param;
    }

    public function getURLParts($param)
    {
        $format = $this->getPathParts($this->format());

        $parts = [];

        foreach($format as $key => $value) {
            if ($value[0] == ':') {
                $formatKey = substr($value, 1);

                if (!array_key_exists($formatKey, $param)) {
                    break;
                }

                $parts[] = urlencode($param[$formatKey]);
            } else {
                $parts[] = urlencode($value);
            }
        }
        unset($value);

        return $parts;
    }

    public function get(array $param = [])
    {
        $defaultParam = $this->param();

        $param = array_diff($param, $defaultParam);

        $allParam = array_replace($defaultParam, $param);

        $format = $this->getPathParts($this->format());

        $formatParam = $this->getFormatParam($allParam);

        // Fetch extend params
        $extendParams = array_diff($param, $formatParam);

        // If no extend params, remove default params from format params
        if (!$extendParams) {
            foreach(array_reverse($format) as $key) {
                if ($key[0] == ':') {
                    $formatKey = substr($key, 1);

                    if (array_key_exists($formatKey, $defaultParam) and $defaultParam[$formatKey] == $formatParam[$formatKey]) {
                        unset($formatParam[$formatKey]);
                    } else {
                        break;
                    }
                } else {
                    break;
                }
            }
        }

        // Get url parts
        $urlParts = $this->getURLParts($formatParam);

        // Add extended params to url parts
        if ($this->extend()) {
            $remain = str_replace('=', '/', http_build_query($extendParams, '', '/'));

            if ($remain) {
                $urlParts[] = $remain;
            }
        }

        $url = '/' . implode('/', $urlParts);

        return $url;
    }

    public function strict($value = null)
    {
        return Obj::fetchBoolVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }
}