<?php

namespace Greg\Http;

use Greg\Support\Engine\InternalTrait;
use Greg\Support\Storage\AccessorTrait;
use Greg\Support\Storage\ArrayAccessTrait;
use Greg\Support\Arr;

class Request extends \Greg\Support\Http\Request implements \ArrayAccess
{
    use AccessorTrait, ArrayAccessTrait, InternalTrait;

    public function __construct(array $params = [])
    {
        $this->storage($params);

        return $this;
    }

    static public function create($appName, array $param = [])
    {
        return static::newInstanceRef($appName, $param);
    }

    public function get($key, $else = null)
    {
        return Arr::get($this->accessor(), $key, $this->getRequest($key, $else));
    }

    public function &getRef($key, $else = null)
    {
        return Arr::getRef($this->accessor(), $key, $this->getRefRequest($key, $else));
    }

    public function getForce($key, $else = null)
    {
        return Arr::getForce($this->accessor(), $key, $this->getForceRequest($key, $else));
    }

    public function &getForceRef($key, $else = null)
    {
        return Arr::getForceRef($this->accessor(), $key, $this->getForceRefRequest($key, $else));
    }

    public function getArray($key, $else = null)
    {
        return Arr::getArray($this->accessor(), $key, $this->getArrayRequest($key, $else));
    }

    public function &getArrayRef($key, $else = null)
    {
        return Arr::getArrayRef($this->accessor(), $key, $this->getArrayRefRequest($key, $else));
    }

    public function getArrayForce($key, $else = null)
    {
        return Arr::getArrayForce($this->accessor(), $key, $this->getArrayForceRequest($key, $else));
    }

    public function &getArrayForceRef($key, $else = null)
    {
        return Arr::getArrayForceRef($this->accessor(), $key, $this->getArrayForceRefRequest($key, $else));
    }

    public function getIndex($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndex($this->accessor(), $index, $this->getIndexRequest($index, $else, $delimiter), $delimiter);
    }

    public function &getIndexRef($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexRef($this->accessor(), $index, $this->getIndexRefRequest($index, $else, $delimiter), $delimiter);
    }

    public function getIndexForce($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexForce($this->accessor(), $index, $this->getIndexForceRequest($index, $else, $delimiter), $delimiter);
    }

    public function &getIndexForceRef($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexForceRef($this->accessor(), $index, $this->getIndexForceRefRequest($index, $else, $delimiter), $delimiter);
    }

    public function getIndexArray($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexArray($this->accessor(), $index, $this->getIndexArrayRequest($index, $else, $delimiter), $delimiter);
    }

    public function &getIndexArrayRef($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexArrayRef($this->accessor(), $index, $this->getIndexArrayRefRequest($index, $else, $delimiter), $delimiter);
    }

    public function getIndexArrayForce($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexArrayForce($this->accessor(), $index, $this->getIndexArrayForceRequest($index, $else, $delimiter), $delimiter);
    }

    public function &getIndexArrayForceRef($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexArrayForceRef($this->accessor(), $index, $this->getIndexArrayForceRefRequest($index, $else, $delimiter), $delimiter);
    }
}