<?php

namespace Greg\Application;

use Greg\Application\Engine\InternalTrait;
use Greg\Application\Http\Request;
use Greg\Application\Http\Response;
use Greg\Application\NotificationCenter\Notifier;

abstract class Controller
{
    use InternalTrait;

    /**
     * @return Response
     */
    protected function response()
    {
        return Response::create($this->appName());
    }

    protected function redirect($location = null)
    {
        $response = $this->response();

        if ($location) {
            $response->location($location);
        }

        return $response;
    }

    /**
     * @param $name
     * @param array $params
     * @param null $code
     * @return Response
     */
    protected function routeRedirect($name, array $params = [], $code = null)
    {
        return $this->response()->route($name, $params, $code);
    }

    /**
     * @return Response
     */
    protected function refresh()
    {
        return $this->response()->refresh();
    }

    /**
     * @return Response
     */
    protected function back()
    {
        return $this->response()->back();
    }

    protected function json($data = [])
    {
        return $this->response()->json($data);
    }

    protected function success($message = null, $data = [])
    {
        return $this->response()->success($message, $data);
    }

    protected function error($message = null, $data = [])
    {
        return $this->response()->error($message, $data);
    }

    protected function translate($key, ...$args)
    {
        return $this->app()->translator()->translate($key, ...$args);
    }

    protected function translateKey($key, $text, ...$args)
    {
        return $this->app()->translator()->translateKey($key, $text, ...$args);
    }

    protected function respondSuccess($message = null)
    {
        if (Request::ajax()) {
            return $this->success($message);
        }

        return $this->back()->with(function(Notifier $notifier) use ($message) {
            $notifier->success($message, [
                'disableTranslation' => true,
            ])->flash();
        });
    }

    protected function respondError($message = null)
    {
        if (Request::ajax()) {
            return $this->error($message);
        }

        return $this->back()->with(function(Notifier $notifier) use ($message) {
            $notifier->error($message, [
                'disableTranslation' => true,
            ])->flash();
        });
    }

    protected function render($name, array $params = [], $layout = null, $_ = null)
    {
        return $this->app()->viewer()->render(...func_get_args());
    }
}