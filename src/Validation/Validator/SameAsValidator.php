<?php

namespace Greg\Validation\Validator;

use Greg\Tool\Arr;
use Greg\Validation\ValidatorInterface;
use Greg\Validation\ValidatorTrait;

class SameAsValidator implements ValidatorInterface
{
    use ValidatorTrait;

    protected $sameAs = null;

    public function __construct($sameAs)
    {
        $this->setSameAs($sameAs);

        return $this;
    }

    public function validate($value, array $values = [])
    {
        $sameAs = $this->getSameAs();

        if ($value !== Arr::get($values, $sameAs)) {
            $this->setError('SameAsError', 'Value is not the same as `' . $sameAs . '`.');

            return false;
        }

        return true;
    }

    public function setSameAs($string)
    {
        $this->sameAs = (string)$string;

        return $this;
    }

    public function getSameAs()
    {
        return $this->sameAs;
    }
}