<?php

namespace Greg\Cache\Storage;

use Greg\Cache\StorageInterface;
use Greg\Cache\StorageTrait;
use Greg\Support\Engine\InternalTrait;
use Greg\Http\Request;
use Greg\Support\Storage\AccessorTrait;
use Greg\Support\Arr;
use Greg\Support\Obj;

class File implements StorageInterface
{
    use AccessorTrait, StorageTrait, InternalTrait;

    protected $path = null;

    protected $schema = 'schema';

    public function __construct($path, $schema = null)
    {
        $this->path($path);

        if ($schema !== null) {
            $this->schema($schema);
        }

        return $this;
    }

    static public function create($appName, $path, $schema = null)
    {
        return static::newInstanceRef($appName, $path, $schema);
    }

    public function init()
    {
        $this->storage = $this->read($this->schema());

        return $this;
    }

    protected function fetchFileName($id)
    {
        return md5($id);
    }

    protected function read($name)
    {
        $file = $this->path() . DIRECTORY_SEPARATOR . $name;

        $exists = file_exists($file);

        if ($exists and !is_readable($file)) {
            throw new \Exception('Cache file `' . $name . '` from `' . $this->schema() . '` is not readable.');
        }

        return $exists ? unserialize(file_get_contents($file)) : null;
    }

    protected function write($name, $data)
    {
        $path = $this->path();

        if (!is_readable($path)) {
            throw new \Exception('Cache path for `' . $this->schema() . '` is not readable.');
        }

        if (!is_writable($path)) {
            throw new \Exception('Cache path for `' . $this->schema() . '` is not writable.');
        }

        $file = $path . DIRECTORY_SEPARATOR . $name;

        if (file_exists($file) and !is_writable($file)) {
            throw new \Exception('Cache file `' . $file . '` from `' . $this->schema() . '` is not writable.');
        }

        file_put_contents($file, serialize($data));

        return $this;
    }

    protected function remove($name)
    {
        $file = $this->path() . DIRECTORY_SEPARATOR . $name;

        if (file_exists($file)) {
            unlink($file);
        }

        return $this;
    }

    protected function add($id)
    {
        Arr::set($this->storage, $id, Request::time());

        $this->update();

        return $this;
    }

    protected function update()
    {
        $this->write($this->schema(), $this->storage);

        return $this;
    }

    public function save($id, $data = null)
    {
        $this->write($this->fetchFileName($id), $data);

        $this->add($id);

        return $this;
    }

    public function has($id)
    {
        return Arr::has($this->storage, $id);
    }

    public function load($id)
    {
        return $this->read($this->fetchFileName($id));
    }

    public function modified($id)
    {
        return Arr::get($this->storage, $id);
    }

    public function delete($ids = [])
    {
        $ids = func_num_args() ? Arr::bring($ids) : array_keys($this->storage);

        foreach($ids as $id) {
            $this->remove($this->fetchFileName($id));
        }

        $this->storage = array_diff($this->storage, $ids);

        $this->update();

        return $this;
    }

    public function path($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function schema($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }
}