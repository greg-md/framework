<?php

namespace Greg\Http;

use Greg\Support\Http\Response;

trait ControllerTrait
{
    protected function response()
    {
        return new Response();
    }

    protected function location($location = null)
    {
        $response = $this->response();

        if ($location) {
            $response->setLocation($location);
        }

        return $response;
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

    protected function download($content, $name = null, $type = null)
    {
        return $this->response()->download($content, $name, $type);
    }
}