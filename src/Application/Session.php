<?php

namespace Greg\Application;

use Greg\Support\Arr;
use Greg\Engine\InternalTrait;
use Greg\Support\Obj;

class Session extends \Greg\Support\Server\Session
{
    use InternalTrait;

    const FLASH_KEY = '_flash_';

    protected $flash = [];

    public function init()
    {
        $this->reloadFlash();

        return $this;
    }

    public function reloadFlash()
    {
        $flash = $this->get(static::FLASH_KEY);

        $this->del(static::FLASH_KEY);

        $this->flash($flash);

        return $this;
    }

    public function flash($key = null, $value = null)
    {
        if ($num = func_num_args()) {
            $flash = &$this->getForceRef(static::FLASH_KEY);

            if (is_array($key)) {
                foreach(($keys = $key) as $key => $value) {
                    $flash[$key] = $value;
                }
            } elseif ($num > 1) {
                $flash[$key] = $value;
            } else {
                unset($flash[$key]);
            }
        }

        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function flashIndex($index = null, $value = null)
    {
        if ($num = func_num_args()) {
            $flash = &$this->getArrayForceRef(static::FLASH_KEY);

            if (is_array($index)) {
                foreach(($indexes = $index) as $index => $value) {
                    Arr::setIndex($flash, $index, $value);
                }
            } elseif ($num > 1) {
                Arr::setIndex($flash, $index, $value);
            } else {
                Arr::delIndex($flash, $index);
            }
        }

        return Obj::fetchArrayVarIndex($this, $this->flash, ...func_get_args());
    }
}