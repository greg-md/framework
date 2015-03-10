<?php

namespace Greg\Storage;

trait Serializable
{
    abstract protected function &accessor(array $accessor = []);

    public function serialize()
    {
        return serialize($this->accessor());
    }

    public function unserialize($storage)
    {
        $this->accessor(unserialize($storage));
    }
}