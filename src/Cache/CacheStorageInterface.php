<?php

namespace Greg\Cache;

interface CacheStorageInterface
{
    public function fetch($id, callable $callable, $expire = 0);

    public function save($id, $data = null);

    public function has($id);

    public function load($id);

    public function getLastModified($id);

    public function isExpired($id, $expire = 0);

    public function delete($ids = []);
}