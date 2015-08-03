<?php

namespace Greg\Support\Cache\Storage;

use Greg\Support\Cache\Storage;
use Greg\Support\Http\Request;
use Greg\Support\Arr;
use Greg\Support\Obj;

/**
 * Class Redis
 * @package Greg\Support\Cache\Storage
 *
 * @method array getKeys($search)
 */
class Redis extends Storage
{
    protected $host = '127.0.0.1';

    protected $port = 6379;

    protected $prefix = null;

    protected $timeout = 0.0;

    protected $adapter = null;

    public function __construct($host = null, $port = null, $prefix = null, $timeout = null)
    {
        if ($host !== null) {
            $this->host($host);
        }

        if ($port !== null) {
            $this->port($port);
        }

        if ($prefix !== null) {
            $this->prefix($prefix);
        }

        if ($timeout !== null) {
            $this->host($timeout);
        }

        return $this;
    }

    public function getAdapter()
    {
        $adapter = $this->adapter();

        if (!$adapter) {
            $adapter = new \Redis;

            $adapter->connect($this->host(), $this->port(), $this->timeout());

            if ($this->prefix()) {
                $adapter->setOption(\Redis::OPT_PREFIX, $this->prefix());
            }

            $this->adapter($adapter);
        }

        return $adapter;
    }

    public function save($id, $data = null)
    {
        $this->getAdapter()->hMset($id, [
            'Content' => serialize($data),
            'LastModified' => Request::time(),
        ]);

        return $this;
    }

    public function has($id)
    {
        return $this->getAdapter()->exists($id);
    }

    public function load($id)
    {
        return unserialize($this->getAdapter()->hGet($id, 'Content'));
    }

    public function modified($id)
    {
        return $this->getAdapter()->hGet($id, 'LastModified');
    }

    public function delete($ids = [])
    {
        $adapter = $this->getAdapter();

        if (func_num_args()) {
            Arr::bringRef($ids);

            $adapter->delete($ids);
        } else {
            $ids = $this->getKeys('*');

            $adapter->setOption(\Redis::OPT_PREFIX, '');

            $adapter->delete($ids);

            $adapter->setOption(\Redis::OPT_PREFIX, $this->prefix());
        }

        return $this;
    }

    public function host($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function port($value = null)
    {
        return Obj::fetchIntVar($this, $this->{__FUNCTION__}, true, ...func_get_args());
    }

    public function prefix($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function timeout($value = null)
    {
        return Obj::fetchFloatVar($this, $this->{__FUNCTION__}, true, ...func_get_args());
    }

    public function adapter(\Redis $value = null)
    {
        return Obj::fetchVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }
}