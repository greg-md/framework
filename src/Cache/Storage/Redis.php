<?php

namespace Greg\Cache\Storage;

use Greg\Cache\StorageInterface;
use Greg\Cache\StorageTrait;
use Greg\Engine\Internal;
use Greg\Http\Request;
use Greg\Support\Arr;
use Greg\Support\Obj;

class Redis extends \Redis implements StorageInterface
{
    use StorageTrait, Internal;

    protected $host = '127.0.0.1';

    protected $port = 6379;

    protected $prefix = null;

    protected $timeout = 0.0;

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

    static public function create($appName, $host = null, $port = null, $prefix = null, $timeout = null)
    {
        return static::newInstanceRef($appName, $host, $port, $prefix, $timeout);
    }

    public function init()
    {
        $this->connect($this->host(), $this->port(), $this->timeout());

        if ($this->prefix()) {
            $this->setOption(Redis::OPT_PREFIX, $this->prefix());
        }

        return $this;
    }

    public function save($id, $data = null)
    {
        $this->hMset($id, [
            'Content' => serialize($data),
            'LastModified' => Request::time(),
        ]);

        return $this;
    }

    public function has($id)
    {
        return $this->exists($id);
    }

    public function load($id)
    {
        return unserialize($this->hGet($id, 'Content'));
    }

    public function modified($id)
    {
        return $this->hGet($id, 'LastModified');
    }

    public function delete($ids = [])
    {
        if (func_num_args()) {
            Arr::bringRef($ids);

            parent::delete($ids);
        } else {
            $ids = $this->getKeys('*');

            $this->setOption(Redis::OPT_PREFIX, '');

            parent::delete($ids);

            $this->setOption(Redis::OPT_PREFIX, $this->prefix());
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
}