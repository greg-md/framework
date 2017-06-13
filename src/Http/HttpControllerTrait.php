<?php

namespace Greg\Framework\Http;

use Greg\Support\Http\Response;

trait HttpControllerTrait
{
    protected function response()
    {
        return new Response();
    }

    protected function location(string $location = null)
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

    protected function json($data)
    {
        $response = $this->response();

        $response->json($data);

        return $response;
    }

    protected function download(string $content, string $name = null, string $type = null)
    {
        $response = $this->response();

        $response->download($content, $name, $type);

        return $response;
    }
}
