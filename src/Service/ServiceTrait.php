<?php

namespace Greg\Service;

trait ServiceTrait
{
    public function error($content = null)
    {
        return $this->newService($content, ServiceResponse::TYPE_ERROR);
    }

    public function success($content = null)
    {
        return $this->newService($content, ServiceResponse::TYPE_SUCCESS);
    }

    protected function newService($content = null, $type = null)
    {
        return new ServiceResponse($content, $type);
    }
}