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

    /**
     * @param $uri
     * @return Response
     */
    public function redirect($uri)
    {
        return $this->response()->location($uri);
    }

    /**
     * @return Response
     */
    public function refresh()
    {
        return $this->response()->refresh();
    }

    /**
     * @return Response
     */
    public function back()
    {
        return $this->response()->back();
    }

    public function json($data = [])
    {
        return $this->response()->json($data);
    }

    public function success($message, $data = [])
    {
        return $this->response()->success($message, $data);
    }

    public function error($message, $data = [])
    {
        return $this->response()->error($message, $data);
    }

    public function renderLayout($name, array $params = [])
    {
        return $this->app()->viewer()->renderLayout($name, $params);
    }

    public function render($name, array $params = [], $layout = null, $_ = null)
    {
        return $this->app()->viewer()->render(...func_get_args());
    }

    public function translate($key, ...$args)
    {
        return $this->app()->translator()->translate($key, ...$args);
    }

    public function translateKey($key, $text, ...$args)
    {
        return $this->app()->translator()->translateKey($key, $text, ...$args);
    }
}