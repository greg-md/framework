<?php

namespace Greg\Cache\Storage;

use Greg\Cache\CacheStorage;
use Greg\Tool\Arr;

class RedisCache extends CacheStorage
{
    private $host = '127.0.0.1';

    private $port = 6379;

    private $prefix = null;

    private $timeout = 0.0;

    private $adapter = null;

    public function __construct($host = null, $port = null, $prefix = null, $timeout = null)
    {
        if ($host !== null) {
            $this->setHost($host);
        }

        if ($port !== null) {
            $this->setPort($port);
        }

        if ($prefix !== null) {
            $this->setPrefix($prefix);
        }

        if ($timeout !== null) {
            $this->setTimeout($timeout);
        }

        return $this;
    }

    public function save($id, $data = null)
    {
        $this->getAdapter()->hMset($id, [
            'Content' => $this->serialize($data),
            'LastModified' => time(),
        ]);

        return $this;
    }

    public function has($id)
    {
        return $this->getAdapter()->exists($id);
    }

    public function load($id)
    {
        return $this->unserialize($this->getAdapter()->hGet($id, 'Content'));
    }

    public function getLastModified($id)
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
            $ids = $adapter->getKeys('*');

            $adapter->setOption(\Redis::OPT_PREFIX, '');

            $adapter->delete($ids);

            $adapter->setOption(\Redis::OPT_PREFIX, $this->getPrefix());
        }

        return $this;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function setHost($name)
    {
        $this->host = (string)$name;

        return $this;
    }

    public function getPort()
    {
        return $this->port;
    }

    public function setPort($number)
    {
        $this->port = (int)$number;

        return $this;
    }

    public function getPrefix()
    {
        return $this->prefix;
    }

    public function setPrefix($name)
    {
        $this->prefix = (string)$name;

        return $this;
    }

    public function getTimeout()
    {
        return $this->timeout;
    }

    public function setTimeout($number)
    {
        $this->timeout = (double)$number;

        return $this;
    }

    public function getAdapter()
    {
        if (!$this->adapter) {
            $this->adapter = new \Redis;

            $this->adapter->connect($this->getHost(), $this->getPort(), $this->getTimeout());

            if ($prefix = $this->getPrefix()) {
                $this->adapter->setOption(\Redis::OPT_PREFIX, $prefix);
            }
        }

        return $this->adapter;
    }

    public function setAdapter(\Redis $adapter)
    {
        $this->adapter = $adapter;

        return $this;
    }
}