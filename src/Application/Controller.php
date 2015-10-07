<?php

namespace Greg\Application;

use Greg\Engine\InternalTrait;
use Greg\Http\Response;

abstract class Controller
{
    use InternalTrait;

    /**
     * @return Response
     */
    public function response()
    {
        return Response::create($this->appName());
    }

    public function redirect($location = null)
    {
        $response = $this->response();

        if ($location) {
            $response->location($location);
        }

        return $response;
    }
}