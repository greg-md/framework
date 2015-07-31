<?php

namespace Greg\Application;

use Greg\Engine\InternalTrait;
use Greg\Http\Response;

class Helper
{
    use InternalTrait;

    /**
     * @return Response
     */
    public function response()
    {
        return Response::create($this->appName());
    }

    /**
     * @param $name
     * @param array $params
     * @param null $code
     * @return Response
     */
    public function routeRedirect($name, array $params = [], $code = null)
    {
        return $this->response()->route($name, $params, $code);
    }

    public function json($data = [])
    {
        return $this->response()->json($data);
    }

    public function renderLayout($name, array $params = [])
    {
        return $this->app()->viewer()->renderLayout($name, $params);
    }

    public function render($name, array $params = [], $layout = null, $_ = null)
    {
        return $this->app()->viewer()->render(...func_get_args());
    }
}