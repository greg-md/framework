<?php

namespace Greg\Application;

use Greg\Engine\Internal;
use Greg\Http\Response;

class Helper
{
    use Internal;

    public function getResource($name, $throw = true)
    {
        return $this->app()->resources()->get($name, $throw);
    }

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
}