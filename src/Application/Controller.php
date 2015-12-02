<?php

namespace Greg\Application;

use Greg\Engine\InternalTrait;
use Greg\Http\Request;
use Greg\Http\Response;
use Greg\NotificationCenter\Notifier;

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

    public function success($message = null, $data = [])
    {
        return $this->response()->success($message, $data);
    }

    public function error($message = null, $data = [])
    {
        return $this->response()->error($message, $data);
    }

    public function translate($key, ...$args)
    {
        return $this->app()->translator()->translate($key, ...$args);
    }

    public function translateKey($key, $text, ...$args)
    {
        return $this->app()->translator()->translateKey($key, $text, ...$args);
    }

    public function respondSuccess($message = null)
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

    public function respondError($message = null)
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
}