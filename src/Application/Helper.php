<?php

namespace Greg\Application;

use Greg\Engine\Internal;
use Greg\Http\Response;

class Helper
{
    use Internal;

    /**
     * @return Response
     */
    public function redirect()
    {
        return Response::create($this->appName());
    }

    public function routeRedirect($name, array $params = [], $code = null)
    {
        return $this->redirect()->route($name, $params, $code);
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