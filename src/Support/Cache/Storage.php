<?php

namespace Greg\Support\Cache;

use Greg\Support\Engine\InternalTrait;
use Greg\Support\Http\Request;

abstract class Storage implements StorageInterface
{
    use InternalTrait;

    public function fetch($id, callable $callable, $expire = 0)
    {
        if ($this->expired($id, $expire)) {
            $this->save($id, $result = $this->callCallable($callable));
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
}