<?php

namespace Greg\Http;

use Greg\Support\Engine\Internal;
use Greg\Support\Storage\Accessor;
use Greg\Support\Storage\ArrayAccess;
use Greg\Support\Arr;

class Request extends \Greg\Support\Http\Request implements \ArrayAccess
{
    use Accessor, ArrayAccess, Internal;

    public function __construct(array $params = [])
    {
        $this->storage($params);

        return $this;
    }

    static public function create($appName, array $param = [])
    {
        return static::newInstanceRef($appName, $param);
    }

    public function &get($key, $else = null)
    {
        return Arr::get($this->accessor(), $key, $this->getRequest($key, $else));
    }

    public function &getRef($key, $else = null)
    {
        return Arr::getRef($this->accessor(), $key, $this->getRefRequest($key, $else));
    }

    public function required($key)
    {
        $value = $this->get($key);

        if (!$value) {
            throw new \Exception('Undefined value for `' . $key . '`.');
        }

        return $value;
    }

    public function getArray($key, $else = null)
    {
        return Arr::getArray($this->accessor(), $key, $this->getArrayRequest($key, $else));
    }

    public function &getIndex($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndex($this->accessor(), $index, $this->getIndexRequest($index, $else, $delimiter), $delimiter);
    }

    public function &getIndexRef($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexRef($this->accessor(), $index, $this->getIndexRefRequest($index, $else, $delimiter), $delimiter);
    }

    public function getIndexArray($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexArray($this->accessor(), $index, $this->getIndexArrayRequest($index, $else, $delimiter), $delimiter);
    }
}