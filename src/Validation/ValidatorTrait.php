<?php

namespace Greg\Validation;

use Greg\Tool\Obj;

trait ValidatorTrait
{
    protected $errors = [];

    public function getErrors()
    {
        return $this->errors();
    }

    protected function errors($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }
}