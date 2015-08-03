<?php

namespace Greg\Support\Cache;

interface StorageInterface
{
    public function fetch($id, callable $callable, $expire = 0);

    public function save($id, $data = null);

    public function has($id);

    public function load($id);

    public function modified($id);

    public function expired($id, $expire = 0);

    public function delete($ids = []);
}