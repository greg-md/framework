<?php

namespace Greg\Cache\Storage;

use Greg\Cache\Exception;
use Greg\Cache\StorageInterface;
use Greg\Cache\StorageTrait;
use Greg\Db\Storage\Sqlite as DbSqlite;
use Greg\Engine\Internal;
use Greg\Http\Request;
use Greg\Support\Obj;

class Sqlite implements StorageInterface
{
    use StorageTrait, Internal;

    protected $path = null;

    protected $adapterClass = '\\Greg\\Db\\Storage\\Sqlite\\Adapter\\Pdo';

    protected $storage = null;

    protected $structureChecked = false;

    public function __construct($path, $adapterClass = null)
    {
        $this->path($path);

        if ($adapterClass !== null) {
            $this->adapterClass($adapterClass);
        }

        return $this;
    }

    public function init()
    {
        $this->storage(DbSqlite::create($this->appName(), $this->path(), $this->adapterClass()));

        $this->checkAndBuildStructure();

        return $this;
    }

    protected function checkAndBuildStructure()
    {
        if (!$this->structureChecked()) {
            if (!$this->checkStructure()) {
                $this->buildStructure();

                if (!$this->checkStructure()) {
                    throw Exception::create($this->appName(), 'Impossible to build SQLite structure.');
                }
            }

            $this->structureChecked(true);
        }

        return $this;
    }

    protected function checkStructure()
    {
        return $this->storage()->select($this->storage()->expr('1'))
                    ->from('sqlite_master')
                    ->whereCol('type', 'table')
                    ->whereCol('name', 'Cache')
                    ->limit(1)
                    ->fetchOne();
    }

    protected function buildStructure()
    {
        $adapter = $this->storage();

        $adapter->exec('CREATE TABLE Cache (Id VARCHAR(32) PRIMARY KEY, Content BLOB, LastModified INTEGER)');

        $adapter->exec('CREATE INDEX CacheLastModified ON Cache(LastModified)');

        return $this;
    }

    public function save($id, $data = null)
    {
        $storage = $this->storage();

        if ($this->has($id)) {
            $storage->update('Cache')
                ->set([
                    'Content' => [serialize($data), $storage::PARAM_LOB],
                    'LastModified' => Request::time(),
                ])
                ->whereCol('Id', md5($id))
                ->exec();
        } else {
            $storage->insert('Cache')
                ->data([
                    'Id' => md5($id),
                    'Content' => [serialize($data), $storage::PARAM_LOB],
                    'LastModified' => Request::time(),
                ])
                ->exec();
        }

        return $this;
    }

    public function has($id)
    {
        return $this->storage()->select($this->storage()->expr('1'))
            ->from('Cache')
            ->whereCol('Id', md5($id))
            ->limit(1)
            ->fetchOne() ? true : false;
    }

    public function load($id)
    {
        $content = $this->storage()->select()
            ->from('Cache', 'Content')
            ->whereCol('Id', md5($id))
            ->limit(1)
            ->fetchOne();

        return $content ? unserialize($content) : null;
    }

    public function modified($id)
    {
        return $this->storage()->select()
            ->from('Cache', 'LastModified')
            ->whereCol('Id', md5($id))
            ->limit(1)
            ->fetchOne();
    }

    public function delete($ids = [])
    {
        $storage = $this->storage();

        $query = $storage->delete('Cache');

        $ids = (array)$ids;
        if ($ids) {
            foreach($ids as &$id) {
                $id = md5($id);
            }
            unset($id);

            $query->whereIn('Id', $ids);
        }

        return $query->exec();
    }

    public function path($value = null, $type = Obj::VAR_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function adapterClass($value = null, $type = Obj::VAR_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    /**
     * @param DbSqlite $value
     * @return DbSqlite|$this|null
     */
    public function storage(DbSqlite $value = null)
    {
        return Obj::fetchVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function structureChecked($value = null)
    {
        return Obj::fetchBoolVar($this, $this->{__FUNCTION__}, func_get_args());
    }
}