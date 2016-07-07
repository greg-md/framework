<?php

namespace Greg\Cache\Storage;

use Greg\Cache\CacheStorage;
use Greg\Http\Request;
use Greg\Tool\Arr;
use Greg\Tool\Obj;

class SqliteCache extends CacheStorage
{
    protected $structureChecked = false;

    protected $path = null;

    protected $adapter = null;

    public function __construct($path)
    {
        $this->path($path);

        return $path;
    }

    public function getAdapter()
    {
        $adapter = $this->adapter();

        if (!$adapter) {
            $adapter = new \PDO('sqlite:' . $this->path());

            $this->adapter($adapter);

            $this->checkAndBuildStructure();
        }

        return $adapter;
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
        $stmt = $this->getAdapter()->query('SELECT 1 FROM sqlite_master WHERE type = "table" and name = "Cache" LIMIT 1');

        return $stmt->fetch() ? true : false;

        /*
        return $this->select(new Expr('1'))
                    ->from('sqlite_master')
                    ->whereCol('type', 'table')
                    ->whereCol('name', 'Cache')
                    ->limit(1)
                    ->one();
        */
    }

    protected function buildStructure()
    {
        $this->getAdapter()->exec('CREATE TABLE Cache (Id VARCHAR(32) PRIMARY KEY, Content BLOB, LastModified INTEGER)');

        $this->getAdapter()->exec('CREATE INDEX CacheLastModified ON Cache(LastModified)');

        return $this;
    }

    public function save($id, $data = null)
    {
        if ($this->has($id)) {
            $stmt = $this->getAdapter()->prepare('UPDATE Cache SET Content = :Content, LastModified = :LastModified WHERE Id = :Id');

            $stmt->bindValue(':Content', serialize($data), \PDO::PARAM_LOB);
            $stmt->bindValue(':LastModified', Request::time());
            $stmt->bindValue(':Id', md5($id));

            $stmt->execute();

            /*
            $this->update('Cache')
                ->set([
                    'Content' => [serialize($data), static::PARAM_LOB],
                    'LastModified' => Request::time(),
                ])
                ->whereCol('Id', md5($id))
                ->exec();
            */
        } else {
            $stmt = $this->getAdapter()->prepare('INSERT INTO Cache(Id, Content, LastModified) VALUES (:Id, :Content, :LastModified)');

            $stmt->bindValue(':Id', md5($id));
            $stmt->bindValue(':Content', serialize($data), \PDO::PARAM_LOB);
            $stmt->bindValue(':LastModified', Request::time());

            $stmt->execute();

            /*
            $this->insert('Cache')
                ->data([
                    'Id' => md5($id),
                    'Content' => [serialize($data), static::PARAM_LOB],
                    'LastModified' => Request::time(),
                ])
                ->exec();
            */
        }

        return $this;
    }

    public function has($id)
    {
        $stmt = $this->getAdapter()->prepare('SELECT 1 FROM Cache WHERE Id = :Id LIMIT 1');

        $stmt->bindValue(':Id', md5($id));

        $stmt->execute();

        return $stmt->fetch() ? true : false;

        /*
        return $this->select(new Expr('1'))
            ->from('Cache')
            ->whereCol('Id', md5($id))
            ->limit(1)
            ->one() ? true : false;
        */
    }

    public function load($id)
    {
        $stmt = $this->getAdapter()->prepare('SELECT Content FROM Cache WHERE Id = :Id LIMIT 1');

        $stmt->bindValue(':Id', md5($id));

        $stmt->execute();

        $row = $stmt->fetch();

        $content = $row ? $row['Content'] : null;

        /*
        $content = $this->select()
            ->from('Cache', 'Content')
            ->whereCol('Id', md5($id))
            ->limit(1)
            ->one();
        */

        return $content ? unserialize($content) : null;
    }

    public function modified($id)
    {
        $stmt = $this->getAdapter()->prepare('SELECT LastModified FROM Cache WHERE Id = :Id LIMIT 1');

        $stmt->bindValue(':Id', md5($id));

        $stmt->execute();

        $row = $stmt->fetch();

        $lastModified = $row ? $row['LastModified'] : null;
        /*
        $lastModified = $this->select()
            ->from('Cache', 'LastModified')
            ->whereCol('Id', md5($id))
            ->limit(1)
            ->one();
        */

        return $lastModified;
    }

    public function delete($ids = [])
    {
        Arr::bringRef($ids);

        if ($ids) {
            foreach($ids as &$id) {
                $id = md5($id);
            }
            unset($id);
        }

        if ($ids) {
            return $this->getAdapter()->exec('DELETE FROM Cache WHERE Id IN (' . implode(', ', $ids) . ')');
        }

        return $this->getAdapter()->exec('DELETE FROM Cache');

        /*
        $query = parent::delete('Cache');

        if ($ids) {
            $query->whereCol('Id', $ids);
        }

        return $query->exec();
        */
    }

    public function structureChecked($value = null)
    {
        return Obj::fetchBoolVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function path($value = null)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function adapter(\PDO $value = null)
    {
        return Obj::fetchVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }
}