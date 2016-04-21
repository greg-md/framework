<?php

namespace Greg\Validation;

use Greg\Engine\InternalTrait;
use Greg\Tool\Arr;
use Greg\Tool\Obj;

class Validator
{
    use InternalTrait;

    protected $validators = [];

    protected $params = [];

    protected $errors = [];

    protected $namespaces = [
        'Greg\\Validation\\Validator',
    ];

    public function __construct(array $validators = [])
    {
        $this->validators($validators);
    }

    public function validate(array $params = [], $validateAll = true)
    {
        $errors = [];

        foreach($this->validators() as $key => $validators) {
            if (!Arr::has($params, $key)) {
                if ($validateAll) {
                    $params[$key] = null;
                } else {
                    continue;
                }
            }

            $value = Arr::get($params, $key);

            $paramErrors = [];

            foreach ($validators as $validator) {
                if (is_array($validator)) {
                    $vName = array_shift($validator);

                    $vArgs = $validator;
                } else {
                    $parts = explode(':', $validator, 2);

                    $vName = array_shift($parts);

                    $vArgs = $parts ? explode(',', array_shift($parts)) : [];
                }

                $className = $this->getClassByName($vName);

                /** @var $class ValidatorInterface */
                $class = $this->loadClassInstance($className, ...$vArgs);

                if (!$class->validate($value, $params)) {
                    $paramErrors = array_merge($paramErrors, $class->getErrors());
                }
            }

            if ($paramErrors) {
                $errors[$key] = $paramErrors;
            }
        }

        $this->params($params);

        if ($errors) {
            $this->errors($errors, true);

            return false;
        }

        return true;
    }

    public function getClassByName($name)
    {
        foreach($this->namespaces() as $namespace) {
            $class = $namespace . '\\' . ucfirst($name) . 'Validator';

            if (class_exists($class)) {
                return $class;
            }
        }

        throw new \Exception('Validator `' . $name . '` not found.');
    }

    public function get($name, $else = null)
    {
        return Arr::get($this->params, $name, $else);
    }

    public function getAll()
    {
        return $this->params();
    }

    public function getErrors()
    {
        return $this->errors();
    }

    protected function validators($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayReplaceVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    protected function params($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayReplaceVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    protected function errors($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    protected function namespaces($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }
}