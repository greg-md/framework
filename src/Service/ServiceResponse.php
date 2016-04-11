<?php

namespace Greg\Service;

use Greg\Tool\Obj;

class ServiceResponse
{
    const TYPE_SUCCESS = 'success';

    const TYPE_ERROR = 'error';

    protected $content = null;

    protected $type = null;

    public function __construct($content = null, $type = null)
    {
        if ($content !== null) {
            $this->content($content);
        }

        if ($type !== null) {
            $this->type($type);
        }

        return $this;
    }

    public function isSuccess()
    {
        return $this->type() === static::TYPE_SUCCESS;
    }

    public function isError()
    {
        return $this->type() === static::TYPE_ERROR;
    }

    /**
     * @param null $value
     * @return mixed
     */
    public function content($value = null)
    {
        return Obj::fetchVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function type($value = null)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }
}