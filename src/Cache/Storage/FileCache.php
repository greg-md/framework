<?php

namespace Greg\Cache\Storage;

use Greg\Cache\CacheStorage;
use Greg\Http\Request;
use Greg\Storage\AccessorTrait;
use Greg\Tool\Arr;

class FileCache extends CacheStorage
{
    use AccessorTrait;

    protected $path = null;

    protected $schemaName = 'schema';

    protected $storage = null;

    public function __construct($path, $schemaName = null)
    {
        $this->setPath($path);

        if ($schemaName !== null) {
            $this->setSchemaName($schemaName);
        }

        return $this;
    }

    protected function storageIsLoaded()
    {
        return $this->storage === null;
    }

    protected function loadStorage()
    {
        if (!$this->storageIsLoaded()) {
            $this->setStorage($this->read($this->getSchemaName()));
        }

        return $this;
    }

    protected function fetchFileName($id)
    {
        return md5($id);
    }

    protected function getFilePath($name)
    {
        return $this->getPath() . DIRECTORY_SEPARATOR . $name;
    }

    protected function read($name)
    {
        $file = $this->getFilePath($name);

        if (!file_exists($file)) {
            return null;
        }

        if (!is_readable($file)) {
            throw new \Exception('Cache file `' . $name . '` from `' . $this->getSchemaName() . '` is not readable.');
        }

        return unserialize(file_get_contents($file));
    }

    protected function write($name, $data)
    {
        $path = $this->getPath();

        if (!is_readable($path)) {
            throw new \Exception('Cache path for `' . $this->getSchemaName() . '` is not readable.');
        }

        if (!is_writable($path)) {
            throw new \Exception('Cache path for `' . $this->getSchemaName() . '` is not writable.');
        }

        $file = $this->getFilePath($name);

        if (file_exists($file) and !is_writable($file)) {
            throw new \Exception('Cache file `' . $file . '` from `' . $this->getSchemaName() . '` is not writable.');
        }

        file_put_contents($file, serialize($data));

        return $this;
    }

    protected function remove($name)
    {
        $file = $this->getFilePath($name);

        if (file_exists($file)) {
            unlink($file);
        }

        return $this;
    }

    protected function add($id)
    {
        $this->loadStorage();

        $this->addToStorage($id, Request::time());

        $this->update();

        return $this;
    }

    protected function update()
    {
        if ($this->storageIsLoaded()) {
            $this->write($this->getSchemaName(), $this->getStorage());
        }

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
        $this->loadStorage();

        return array_key_exists($id, $this->storage);
    }

    public function load($id)
    {
        return $this->read($this->fetchFileName($id));
    }

    public function getLastModified($id)
    {
        $this->loadStorage();

        return $this->getStorageValue($id);
    }

    public function delete($ids = [])
    {
        $this->loadStorage();

        $ids = func_num_args() ? Arr::bring($ids) : array_keys($this->storage);

        foreach($ids as $id) {
            $this->remove($this->fetchFileName($id));
        }

        $this->setStorage(array_diff($this->getStorage(), $ids));

        $this->update();

        return $this;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setPath($path)
    {
        $this->path = (string)$path;

        return $this;
    }

    public function getSchemaName()
    {
        return $this->schemaName;
    }

    public function setSchemaName($name)
    {
        $this->schemaName = (string)$name;

        return $this;
    }
}