<?php

namespace Greg\Cache\Storage;

use Greg\Cache\StorageInterface;
use Greg\Cache\StorageTrait;
use Greg\Db\Sql\Storage\Sqlite\Adapter\Pdo;
use Greg\Http\Request;
use Greg\Support\Arr;
use Greg\Support\Obj;

class Sqlite extends \Greg\Db\Sql\Storage\Sqlite implements StorageInterface
{
    use StorageTrait;

    protected $structureChecked = false;

    public function init()
    {
        $this->checkAndBuildStructure();

        return $this;
    }

    protected function checkAndBuildStructure()
    {
        if (!$this->structureChecked()) {
            if (!$this->checkStructure()) {
                $this->buildStructure();

                if (!$this->checkStructure()) {
                    throw new \Exception('Impossible to build SQLite structure.');
                }
            }

            $this->structureChecked(true);
        }

        return $this;
    }

    protected function checkStructure()
    {
        return $this->select($this->expr('1'))
                    ->from('sqlite_master')
                    ->whereCol('type', 'table')
                    ->whereCol('name', 'Cache')
                    ->limit(1)
                    ->one();
    }

    protected function buildStructure()
    {
        $this->exec('CREATE TABLE Cache (Id VARCHAR(32) PRIMARY KEY, Content BLOB, LastModified INTEGER)');

        $this->exec('CREATE INDEX CacheLastModified ON Cache(LastModified)');

        return $this;
    }

    public function save($id, $data = null)
    {
        if ($this->has($id)) {
            $this->update('Cache')
                ->set([
                    'Content' => [serialize($data), static::PARAM_LOB],
                    'LastModified' => Request::time(),
                ])
                ->whereCol('Id', md5($id))
                ->exec();
        } else {
            $this->insert('Cache')
                ->data([
                    'Id' => md5($id),
                    'Content' => [serialize($data), static::PARAM_LOB],
                    'LastModified' => Request::time(),
                ])
                ->exec();
        }

        return $this;
    }

    public function has($id)
    {
        return $this->select($this->expr('1'))
            ->from('Cache')
            ->whereCol('Id', md5($id))
            ->limit(1)
            ->one() ? true : false;
    }

    public function load($id)
    {
        $content = $this->select()
            ->from('Cache', 'Content')
            ->whereCol('Id', md5($id))
            ->limit(1)
            ->one();

        return $content ? unserialize($content) : null;
    }

    public function modified($id)
    {
        return $this->select()
            ->from('Cache', 'LastModified')
            ->whereCol('Id', md5($id))
            ->limit(1)
            ->one();
    }

    public function delete($ids = [])
    {
        $query = parent::delete('Cache');

        Arr::bringRef($ids);

        if ($ids) {
            foreach($ids as &$id) {
                $id = md5($id);
            }
            unset($id);

            $query->whereCol('Id', $ids);
        }

        return $query->exec();
    }

    public function structureChecked($value = null)
    {
        return Obj::fetchBoolVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }
}