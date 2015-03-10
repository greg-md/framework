<?php

namespace Greg\Cache;

use Greg\Engine\Internal;
use Greg\Support\Obj;

/**
 * Class Manager
 * @package Greg\Cache
 *
 * Call methods
 *
 * @method fetch($id, $callback, $expire = 0);
 * @method save($id, $data = null);
 * @method has($id);
 * @method load($id);
 * @method modified($id);
 * @method expired($id, $expire = 0);
 * @method delete($ids = []);
 */
class Manager
{
    use \Greg\Engine\Manager, Internal;

    public function storage(StorageInterface $value = null)
    {
        return Obj::fetchVar($this, $this->{__FUNCTION__}, func_get_args());
    }
}