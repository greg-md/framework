<?php

namespace Greg\Cache;

use Greg\Engine\Adapter;
use Greg\Engine\Internal;
use Greg\Support\Obj;

/**
 * Class Manager
 * @package Greg\Cache
 *
 * Call methods
 *
 * @method fetch($id, callable $callable, $expire = 0);
 * @method save($id, $data = null);
 * @method has($id);
 * @method load($id);
 * @method modified($id);
 * @method expired($id, $expire = 0);
 * @method delete($ids = []);
 */
class Manager
{
    use Adapter, Internal;

    public function __construct($adapter)
    {
        $this->adapter($adapter);

        return $this;
    }
}