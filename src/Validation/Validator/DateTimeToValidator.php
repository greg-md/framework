<?php

namespace Greg\Validation\Validator;

use Greg\System\DateTime;
use Greg\Tool\Obj;
use Greg\Validation\ValidatorInterface;
use Greg\Validation\ValidatorTrait;

class DateTimeToValidator implements ValidatorInterface
{
    use ValidatorTrait;

    protected $to = null;

    protected $includeTo = true;

    public function __construct($to, $includeTo = null)
    {
        $this->to($to);

        if ($includeTo !== null) {
            $this->includeTo($includeTo);
        }

        return $this;
    }

    public function validate($value, array $values = [])
    {
        if (!$value) {
            return true;
        }

        $errors = [];

        $value = DateTime::toTimestamp($value);

        $to = DateTime::toTimestamp($this->to());

        if ($this->includeTo() ? $value >= $to : $value > $to) {
            $errors[] = 'Value should be less than ' . DateTime::toStringDateTime($to) . '.';
        }

        if ($errors) {
            $this->errors($errors, true);

            return false;
        }

        return true;
    }

    public function to($value = null)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function includeTo($value = null)
    {
        return Obj::fetchBoolVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }
}