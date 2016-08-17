<?php

namespace Greg\Cache\Storage;

use Greg\Cache\CacheStorage;

class SqliteCache extends CacheStorage
{
    private $path = null;

    private $structureChecked = false;

    private $adapter = null;

    public function __construct($path = null)
    {
        if ($path !== null) {
            $this->setPath($path);
        }

        return $path;
    }

    protected function checkAndBuildStructure()
    {
        if (!$this->structureChecked) {
            if (!$this->checkStructure()) {
                $this->buildStructure();

                if (!$this->checkStructure()) {
                    throw new \Exception('Impossible to build SQLite structure.');
                }
            }

            $this->structureChecked = true;
        }

        return $this;
    }

    protected function checkStructure()
    {
        $stmt = $this->getAdapter()->query('SELECT 1 FROM sqlite_master WHERE type = "table" and name = "Cache" LIMIT 1');

        return $stmt->fetch() ? true : false;
    }

    protected function buildStructure()
    {
        $this->getAdapter()->exec('CREATE TABLE Cache (Id VARCHAR(32) PRIMARY KEY, Content BLOB, LastModified INTEGER)');

        $this->getAdapter()->exec('CREATE INDEX CacheLastModified ON Cache(LastModified)');

        return $this;
    }

    protected function fetchId($id)
    {
        return md5($id);
    }

    public function save($id, $data = null)
    {
        if ($this->has($id)) {
            $stmt = $this->getAdapter()->prepare('UPDATE Cache SET Content = :Content, LastModified = :LastModified WHERE Id = :Id');

            $stmt->bindValue(':Content', $this->serialize($data), \PDO::PARAM_LOB);
            $stmt->bindValue(':LastModified', time());
            $stmt->bindValue(':Id', $this->fetchId($id));

            $stmt->execute();
        } else {
            $stmt = $this->getAdapter()->prepare('INSERT INTO Cache(Id, Content, LastModified) VALUES (:Id, :Content, :LastModified)');

            $stmt->bindValue(':Id', $this->fetchId($id));
            $stmt->bindValue(':Content', $this->serialize($data), \PDO::PARAM_LOB);
            $stmt->bindValue(':LastModified', time());

            $stmt->execute();
        }

        return $this;
    }

    public function has($id)
    {
        $stmt = $this->getAdapter()->prepare('SELECT 1 FROM Cache WHERE Id = :Id LIMIT 1');

        $stmt->bindValue(':Id', $this->fetchId($id));

        $stmt->execute();

        return $stmt->fetch() ? true : false;
    }

    public function load($id)
    {
        $stmt = $this->getAdapter()->prepare('SELECT Content FROM Cache WHERE Id = :Id LIMIT 1');

        $stmt->bindValue(':Id', $this->fetchId($id));

        $stmt->execute();

        $row = $stmt->fetch();

        $content = $row ? $row['Content'] : null;

        return $content ? unserialize($content) : null;
    }

    public function getLastModified($id)
    {
        $stmt = $this->getAdapter()->prepare('SELECT LastModified FROM Cache WHERE Id = :Id LIMIT 1');

        $stmt->bindValue(':Id', $this->fetchId($id));

        $stmt->execute();

        $row = $stmt->fetch();

        $lastModified = $row ? $row['LastModified'] : null;

        return $lastModified;
    }

    public function delete($ids = [])
    {
        $ids = (array)$ids;

        if ($ids) {
            foreach($ids as &$id) {
                $id = $this->fetchId($id);
            }
            unset($id);

            return $this->getAdapter()->exec('DELETE FROM Cache WHERE Id IN (' . implode(', ', $ids) . ')');
        }

        return $this->getAdapter()->exec('DELETE FROM Cache');
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setPath($name)
    {
        $this->path = (string)$name;

        return $this;
    }

    public function getAdapter()
    {
        if (!$this->adapter) {
            $this->adapter = new \PDO('sqlite:' . $this->getPath());

            $this->checkAndBuildStructure();
        }

        return $this->adapter;
    }

    public function setAdapter(\PDO $adapter)
    {
        $this->adapter = $adapter;

        return $this;
    }
}