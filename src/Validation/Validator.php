<?php

namespace Greg\Validation;

use Greg\Support\Arr;
use Greg\Support\Obj;

class Validator
{
    /**
     * @var ValidatorInterface[][]
     */
    protected $validators = [];

    protected $params = [];

    protected $errors = [];

    protected $namespaces = [
        'Greg\\Validation\\Validator',
    ];

    public function validate(array $params = [])
    {
        $this->setParams($params);

        foreach ($this->getValidators() as $key => $validators) {
            foreach ($validators as $validator) {
                if (!$validator->validate(Arr::getRef($params, $key), $params)) {
                    $this->addErrors($key, $validator->getErrors());
                }
            }
        }

        return !$this->hasErrors();
    }

    public function getClassByName($name)
    {
        foreach ($this->getNamespaces() as $namespace) {
            $className = $namespace . '\\' . ucfirst($name) . 'Validator';

            if (class_exists($className)) {
                return $className;
            }
        }

        throw new \Exception('Validator `' . $name . '` not found.');
    }

    public function addValidator($key, $validator)
    {
        if (!is_object($validator)) {
            if (is_array($validator)) {
                $name = array_shift($validator);

                $args = $validator;
            } else {
                $parts = explode(':', $validator, 2);

                $name = array_shift($parts);

                $args = $parts ? explode(',', array_shift($parts)) : [];
            }

            $className = $this->getClassByName($name);

            $validator = Obj::loadInstance($className, ...$args);
        }

        if (!($validator instanceof ValidatorInterface)) {
            throw new \Exception('Validator should be an instance of `ValidatorInterface`.');
        }

        $this->validators[$key][] = $validator;
    }

    public function getValidators()
    {
        return $this->validators;
    }

    protected function setParams(array $params)
    {
        $this->params = $params;

        return $this;
    }

    public function getParam($name, $else = null)
    {
        return Arr::getRef($this->getParams(), $name, $else);
    }

    public function getParams()
    {
        return $this->params;
    }

    protected function addErrors($key, array $errors)
    {
        if (!isset($this->errors[$key])) {
            $this->errors[$key] = $errors;
        } else {
            $this->errors[$key] = array_merge($this->errors[$key], $errors);
        }

        return $this;
    }

    protected function setErrors($key, array $errors)
    {
        $this->errors[$key] = $errors;

        return $this;
    }

    public function hasErrors()
    {
        return (bool) $this->errors;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getNamespaces()
    {
        return $this->namespaces;
    }
}
