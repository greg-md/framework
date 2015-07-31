<?php

namespace Greg\Support\Http;

use Greg\Support\Server;
use Greg\Support\Arr;
use Greg\Support\Storage\AccessorTrait;
use Greg\Support\Storage\ArrayAccessTrait;

class Request implements \ArrayAccess
{
    use AccessorTrait, ArrayAccessTrait;

    const URI_ALL =  'all';

    const URI_PATH =  'path';

    const URI_QUERY =  'query';

    public function __construct(array $params = [])
    {
        $this->storage($params);

        return $this;
    }

    public function get($key, $else = null)
    {
        return Arr::get($this->accessor(), $key, $this->getRequest($key, $else));
    }

    public function &getRef($key, $else = null)
    {
        return Arr::getRef($this->accessor(), $key, $this->getRefRequest($key, $else));
    }

    public function getForce($key, $else = null)
    {
        return Arr::getForce($this->accessor(), $key, $this->getForceRequest($key, $else));
    }

    public function &getForceRef($key, $else = null)
    {
        return Arr::getForceRef($this->accessor(), $key, $this->getForceRefRequest($key, $else));
    }

    public function getArray($key, $else = null)
    {
        return Arr::getArray($this->accessor(), $key, $this->getArrayRequest($key, $else));
    }

    public function &getArrayRef($key, $else = null)
    {
        return Arr::getArrayRef($this->accessor(), $key, $this->getArrayRefRequest($key, $else));
    }

    public function getArrayForce($key, $else = null)
    {
        return Arr::getArrayForce($this->accessor(), $key, $this->getArrayForceRequest($key, $else));
    }

    public function &getArrayForceRef($key, $else = null)
    {
        return Arr::getArrayForceRef($this->accessor(), $key, $this->getArrayForceRefRequest($key, $else));
    }

    public function getIndex($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndex($this->accessor(), $index, $this->getIndexRequest($index, $else, $delimiter), $delimiter);
    }

    public function &getIndexRef($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexRef($this->accessor(), $index, $this->getIndexRefRequest($index, $else, $delimiter), $delimiter);
    }

    public function getIndexForce($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexForce($this->accessor(), $index, $this->getIndexForceRequest($index, $else, $delimiter), $delimiter);
    }

    public function &getIndexForceRef($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexForceRef($this->accessor(), $index, $this->getIndexForceRefRequest($index, $else, $delimiter), $delimiter);
    }

    public function getIndexArray($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexArray($this->accessor(), $index, $this->getIndexArrayRequest($index, $else, $delimiter), $delimiter);
    }

    public function &getIndexArrayRef($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexArrayRef($this->accessor(), $index, $this->getIndexArrayRefRequest($index, $else, $delimiter), $delimiter);
    }

    public function getIndexArrayForce($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexArrayForce($this->accessor(), $index, $this->getIndexArrayForceRequest($index, $else, $delimiter), $delimiter);
    }

    public function &getIndexArrayForceRef($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexArrayForceRef($this->accessor(), $index, $this->getIndexArrayForceRefRequest($index, $else, $delimiter), $delimiter);
    }

    static public function protocol()
    {
        return Server::get('SERVER_PROTOCOL');
    }

    static public function clientHost()
    {
        return Server::get('HTTP_HOST');
    }

    static public function serverHost()
    {
        return Server::get('SERVER_NAME');
    }

    static public function serverAdmin()
    {
        return Server::get('SERVER_ADMIN');
    }

    static public function secured()
    {
        return Server::get('HTTPS');
    }

    static public function isSecured()
    {
        return static::secured() == 'on';
    }

    static public function with()
    {
        return Server::get('HTTP_X_REQUESTED_WITH');
    }

    static public function port()
    {
        return Server::get('SERVER_PORT');
    }

    static public function agent()
    {
        return Server::get('HTTP_USER_AGENT');
    }

    static public function ip()
    {
        return Server::get('REMOTE_ADDR');
    }

    static public function uri($flag = self::URI_ALL)
    {
        switch($flag) {
            case static::URI_PATH;
                return static::uriPath();

            case static::URI_QUERY;
                return static::uriQuery();
        }

        return Server::get('REQUEST_URI');
    }

    static public function baseUri()
    {
        $scriptName = Server::scriptName();

        $uriInfo = pathinfo($scriptName);

        $baseUri = $uriInfo['dirname'];

        if (DIRECTORY_SEPARATOR != '/') {
            $baseUri = str_replace(DIRECTORY_SEPARATOR, '/', $baseUri);
        }

        if ($baseUri[0] == '.') {
            $baseUri[0] = '/';
        }

        if ($baseUri == '/') {
            $baseUri = null;
        }

        return $baseUri;
    }

    static public function uriPath()
    {
        list($path) = explode('?', static::uri(), 2);

        return $path;
    }

    static public function uriQuery()
    {
        list($path, $query) = explode('?', static::uri(), 2);

        unset($path);

        return $query;
    }

    static public function referrer()
    {
        return Server::get('HTTP_REFERER');
    }

    static public function modifiedSince()
    {
        return Server::get('HTTP_IF_MODIFIED_SINCE');
    }

    static public function match()
    {
        return Server::get('HTTP_IF_NONE_MATCH');
    }

    static public function time()
    {
        return Server::requestTime();
    }

    static public function microTime()
    {
        return Server::requestMicroTime();
    }

    static public function ajax()
    {
        return static::with() == 'XMLHttpRequest';
    }

    static public function isGet()
    {
        return (bool)$_GET;
    }

    static public function &allGet()
    {
        return $_GET;
    }

    // Start standard $_GET array methods

    static public function hasGet($key, ...$keys)
    {
        return Arr::has($_GET, $key, ...$keys);
    }

    static public function hasIndexGet($index, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::hasIndex($_GET, $index, $delimiter);
    }

    static public function setGet($key, $value)
    {
        return Arr::set($_GET, $key, $value);
    }

    static public function setRefGet($key, &$value)
    {
        return Arr::setRef($_GET, $key, $value);
    }

    static public function setIndexGet($index, $value, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::setIndex($_GET, $index, $value, $delimiter);
    }

    static public function setIndexRefGet($index, &$value, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::setIndexRef($_GET, $index, $value, $delimiter);
    }

    static public function getGet($key, $else = null)
    {
        return Arr::get($_GET, $key, $else);
    }

    static public function &getRefGet($key, $else = null)
    {
        return Arr::getRef($_GET, $key, $else);
    }

    static public function getForceGet($key, $else = null)
    {
        return Arr::getForce($_GET, $key, $else);
    }

    static public function &getForceRefGet($key, $else = null)
    {
        return Arr::getForceRef($_GET, $key, $else);
    }

    static public function getArrayGet($key, $else = null)
    {
        return Arr::getArray($_GET, $key, $else);
    }

    static public function &getArrayRefGet($key, $else = null)
    {
        return Arr::getArrayRef($_GET, $key, $else);
    }

    static public function getArrayForceGet($key, $else = null)
    {
        return Arr::getArrayForce($_GET, $key, $else);
    }

    static public function &getArrayForceRefGet($key, $else = null)
    {
        return Arr::getArrayForceRef($_GET, $key, $else);
    }

    static public function getIndexGet($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndex($_GET, $index, $else, $delimiter);
    }

    static public function &getIndexRefGet($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexRef($_GET, $index, $else, $delimiter);
    }

    static public function getIndexForceGet($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexForce($_GET, $index, $else, $delimiter);
    }

    static public function &getIndexForceRefGet($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexForceRef($_GET, $index, $else, $delimiter);
    }

    static public function getIndexArrayGet($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexArray($_GET, $index, $else, $delimiter);
    }

    static public function &getIndexArrayRefGet($index, $else = null)
    {
        return Arr::getIndexArrayRef($_GET, $index, $else);
    }

    static public function getIndexArrayForceGet($index, $else = null)
    {
        return Arr::getIndexArrayForce($_GET, $index, $else);
    }

    static public function &getIndexArrayForceRefGet($index, $else = null)
    {
        return Arr::getIndexArrayForceRef($_GET, $index, $else);
    }

    static public function requiredGet($key)
    {
        return Arr::required($_GET, $key);
    }

    static public function &requiredRefGet($key)
    {
        return Arr::requiredRef($_GET, $key);
    }

    static public function delGet($key, ...$keys)
    {
        return Arr::del($_GET, $key, ...$keys);
    }

    static public function indexDelGet($index, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::delIndex($_GET, $index, $delimiter);
    }

    // End standard $_GET array methods

    static public function isPost()
    {
        return (bool)$_POST;
    }

    static public function &allPost()
    {
        return $_POST;
    }

    // Start standard $_POST array methods

    static public function hasPost($key, ...$keys)
    {
        return Arr::has($_POST, $key, ...$keys);
    }

    static public function hasIndexPost($index, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::hasIndex($_POST, $index, $delimiter);
    }

    static public function setPost($key, $value)
    {
        return Arr::set($_POST, $key, $value);
    }

    static public function setRefPost($key, &$value)
    {
        return Arr::setRef($_POST, $key, $value);
    }

    static public function setIndexPost($index, $value, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::setIndex($_POST, $index, $value, $delimiter);
    }

    static public function setIndexRefPost($index, &$value, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::setIndexRef($_POST, $index, $value, $delimiter);
    }

    static public function getPost($key, $else = null)
    {
        return Arr::get($_POST, $key, $else);
    }

    static public function &getRefPost($key, $else = null)
    {
        return Arr::getRef($_POST, $key, $else);
    }

    static public function getForcePost($key, $else = null)
    {
        return Arr::getForce($_POST, $key, $else);
    }

    static public function &getForceRefPost($key, $else = null)
    {
        return Arr::getForceRef($_POST, $key, $else);
    }

    static public function getArrayPost($key, $else = null)
    {
        return Arr::getArray($_POST, $key, $else);
    }

    static public function &getArrayRefPost($key, $else = null)
    {
        return Arr::getArrayRef($_POST, $key, $else);
    }

    static public function getArrayForcePost($key, $else = null)
    {
        return Arr::getArrayForce($_POST, $key, $else);
    }

    static public function &getArrayForceRefPost($key, $else = null)
    {
        return Arr::getArrayForceRef($_POST, $key, $else);
    }

    static public function getIndexPost($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndex($_POST, $index, $else, $delimiter);
    }

    static public function &getIndexRefPost($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexRef($_POST, $index, $else, $delimiter);
    }

    static public function getIndexForcePost($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexForce($_POST, $index, $else, $delimiter);
    }

    static public function &getIndexForceRefPost($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexForceRef($_POST, $index, $else, $delimiter);
    }

    static public function getIndexArrayPost($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexArray($_POST, $index, $else, $delimiter);
    }

    static public function &getIndexArrayRefPost($index, $else = null)
    {
        return Arr::getIndexArrayRef($_POST, $index, $else);
    }

    static public function getIndexArrayForcePost($index, $else = null)
    {
        return Arr::getIndexArrayForce($_POST, $index, $else);
    }

    static public function &getIndexArrayForceRefPost($index, $else = null)
    {
        return Arr::getIndexArrayForceRef($_POST, $index, $else);
    }

    static public function requiredPost($key)
    {
        return Arr::required($_POST, $key);
    }

    static public function &requiredRefPost($key)
    {
        return Arr::requiredRef($_POST, $key);
    }

    static public function delPost($key, ...$keys)
    {
        return Arr::del($_POST, $key, ...$keys);
    }

    static public function indexDelPost($index, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::delIndex($_POST, $index, $delimiter);
    }

    // End standard $_POST array methods

    static public function isRequest()
    {
        return (bool)$_REQUEST;
    }

    static public function &allRequest()
    {
        return $_REQUEST;
    }

    // Start standard $_REQUEST array methods

    static public function hasRequest($key, ...$keys)
    {
        return Arr::has($_REQUEST, $key, ...$keys);
    }

    static public function hasIndexRequest($index, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::hasIndex($_REQUEST, $index, $delimiter);
    }

    static public function setRequest($key, $value)
    {
        return Arr::set($_REQUEST, $key, $value);
    }

    static public function setRefRequest($key, &$value)
    {
        return Arr::setRef($_REQUEST, $key, $value);
    }

    static public function setIndexRequest($index, $value, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::setIndex($_REQUEST, $index, $value, $delimiter);
    }

    static public function setIndexRefRequest($index, &$value, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::setIndexRef($_REQUEST, $index, $value, $delimiter);
    }

    static public function getRequest($key, $else = null)
    {
        return Arr::get($_REQUEST, $key, $else);
    }

    static public function &getRefRequest($key, $else = null)
    {
        return Arr::getRef($_REQUEST, $key, $else);
    }

    static public function getForceRequest($key, $else = null)
    {
        return Arr::getForce($_REQUEST, $key, $else);
    }

    static public function &getForceRefRequest($key, $else = null)
    {
        return Arr::getForceRef($_REQUEST, $key, $else);
    }

    static public function getArrayRequest($key, $else = null)
    {
        return Arr::getArray($_REQUEST, $key, $else);
    }

    static public function &getArrayRefRequest($key, $else = null)
    {
        return Arr::getArrayRef($_REQUEST, $key, $else);
    }

    static public function getArrayForceRequest($key, $else = null)
    {
        return Arr::getArrayForce($_REQUEST, $key, $else);
    }

    static public function &getArrayForceRefRequest($key, $else = null)
    {
        return Arr::getArrayForceRef($_REQUEST, $key, $else);
    }

    static public function getIndexRequest($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndex($_REQUEST, $index, $else, $delimiter);
    }

    static public function &getIndexRefRequest($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexRef($_REQUEST, $index, $else, $delimiter);
    }

    static public function getIndexForceRequest($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexForce($_REQUEST, $index, $else, $delimiter);
    }

    static public function &getIndexForceRefRequest($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexForceRef($_REQUEST, $index, $else, $delimiter);
    }

    static public function getIndexArrayRequest($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexArray($_REQUEST, $index, $else, $delimiter);
    }

    static public function &getIndexArrayRefRequest($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexArrayRef($_REQUEST, $index, $else, $delimiter);
    }

    static public function getIndexArrayForceRequest($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexArrayForce($_REQUEST, $index, $else, $delimiter);
    }

    static public function &getIndexArrayForceRefRequest($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexArrayForceRef($_REQUEST, $index, $else, $delimiter);
    }

    static public function requiredRequest($key)
    {
        return Arr::required($_REQUEST, $key);
    }

    static public function &requiredRefRequest($key)
    {
        return Arr::requiredRef($_REQUEST, $key);
    }

    static public function delRequest($key, ...$keys)
    {
        return Arr::del($_REQUEST, $key, ...$keys);
    }

    static public function indexDelRequest($index, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::delIndex($_REQUEST, $index, $delimiter);
    }

    // End standard $_REQUEST array methods

    static public function delAll($key, ...$keys)
    {
        Arr::del($_GET, $key, ...$keys);

        Arr::del($_POST, $key, ...$keys);

        Arr::del($_REQUEST, $key, ...$keys);

        return true;
    }
}