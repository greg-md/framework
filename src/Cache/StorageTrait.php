<?php

namespace Greg\Cache;

use Greg\Application\Runner;
use Greg\Http\Request;

trait StorageTrait
{
    public function fetch($id, $callback, $expire = 0)
    {
        if ($this->expired($id, $expire)) {
            $this->save($id, $result = $this->app()->binder()->call($callback));
        } else {
            $result = $this->load($id);
        }

        return $result;
    }

    public function expired($id, $expire = 0)
    {
        $modified = $this->modified($id);

        if ($modified === null or $modified === false) {
            return true;
        }

        if (!ctype_digit((string)$expire)) {
            $expire = strtotime($expire, $modified) - $modified;
        }

        if (($expire > 0 and ($modified + $expire) <= Request::time()) or $expire < 0) {
            $this->delete($id);

            return true;
        }

        return false;
    }

    /**
     * @param Runner $app
     * @return Runner
     */
    abstract public function app(Runner $app = null);

    abstract public function save($id, $data = null);

    abstract public function load($id);

    abstract public function modified($id);

    abstract public function delete($ids = []);
}