<?php

namespace Greg\Support\Storage;

trait SerializableTrait
{
    abstract protected function &accessor(array $storage = []);

    public function serialize()
    {
        return serialize($this->accessor());
    }

    public function unserialize($storage)
    {
        $this->accessor(unserialize($storage));
    }
}