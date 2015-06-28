<?php

namespace Greg\Support\Http;

use Greg\Support\Server\Info;
use Greg\Support\Arr;

class Request
{
    static public function protocol()
    {
        return Info::get('SERVER_PROTOCOL');
    }

    static public function clientHost()
    {
        return Info::get('HTTP_HOST');
    }

    static public function serverHost()
    {
        return Info::get('SERVER_NAME');
    }

    static public function serverAdmin()
    {
        return Info::get('SERVER_ADMIN');
    }

    static public function secured()
    {
        return Info::get('HTTPS');
    }

    static public function isSecured()
    {
        return static::secured() == 'on';
    }

    static public function with()
    {
        return Info::get('HTTP_X_REQUESTED_WITH');
    }

    static public function port()
    {
        return Info::get('SERVER_PORT');
    }

    static public function agent()
    {
        return Info::get('HTTP_USER_AGENT');
    }

    static public function ip()
    {
        return Info::get('REMOTE_ADDR');
    }

    static public function uri()
    {
        return Info::get('REQUEST_URI');
    }

    static public function referrer()
    {
        return Info::get('HTTP_REFERER');
    }

    static public function modifiedSince()
    {
        return Info::get('HTTP_IF_MODIFIED_SINCE');
    }

    static public function match()
    {
        return Info::get('HTTP_IF_NONE_MATCH');
    }

    static public function time()
    {
        return Info::requestTime();
    }

    static public function microTime()
    {
        return Info::requestMicroTime();
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
        return Arr::set($_GET, $key, $value);
    }

    static public function setIndexGet($index, $value, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::setIndex($_GET, $index, $value, $delimiter);
    }

    static public function setIndexRefGet($index, &$value, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::setIndex($_GET, $index, $value, $delimiter);
    }

    static public function getGet($key, $else = null)
    {
        return Arr::get($_GET, $key, $else);
    }

    static public function &getRefGet($key, $else = null)
    {
        return Arr::getRef($_GET, $key, $else);
    }

    static public function requiredGet($key)
    {
        $value = static::getGet($key);

        if (!$value) {
            throw new \Exception('Undefined value for `' . $key . '`.');
        }

        return $value;
    }

    static public function getArrayGet($key, $else = null)
    {
        return Arr::getArray($_GET, $key, $else);
    }

    static public function &getIndexGet($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndex($_GET, $index, $else, $delimiter);
    }

    static public function &getIndexRefGet($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexRef($_GET, $index, $else, $delimiter);
    }

    static public function getIndexArrayGet($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexArray($_GET, $index, $else, $delimiter);
    }

    static public function delGet($key, ...$keys)
    {
        return Arr::del($_GET, $key, ...$keys);
    }

    static public function indexDelGet($index, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::delIndex($_GET, $index, $delimiter);
    }

    static public function isPost()
    {
        return (bool)$_POST;
    }

    static public function &allPost()
    {
        return $_POST;
    }

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
        return Arr::set($_POST, $key, $value);
    }

    static public function setIndexPost($index, $value, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::setIndex($_POST, $index, $value, $delimiter);
    }

    static public function setIndexRefPost($index, &$value, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::setIndex($_POST, $index, $value, $delimiter);
    }

    static public function getPost($key, $else = null)
    {
        return Arr::get($_POST, $key, $else);
    }

    static public function &getRefPost($key, $else = null)
    {
        return Arr::getRef($_POST, $key, $else);
    }

    static public function requiredPost($key)
    {
        $value = static::getPost($key);

        if (!$value) {
            throw new \Exception('Undefined value for `' . $key . '`.');
        }

        return $value;
    }

    static public function getArrayPost($key, $else = null)
    {
        return Arr::getArray($_POST, $key, $else);
    }

    static public function &getIndexPost($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndex($_POST, $index, $else, $delimiter);
    }

    static public function &getIndexRefPost($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexRef($_POST, $index, $else, $delimiter);
    }

    static public function getIndexArrayPost($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexArray($_POST, $index, $else, $delimiter);
    }

    static public function delPost($key, ...$keys)
    {
        return Arr::del($_POST, $key, ...$keys);
    }

    static public function indexDelPost($index, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::delIndex($_POST, $index, $delimiter);
    }

    static public function isRequest()
    {
        return (bool)$_REQUEST;
    }

    static public function &allRequest()
    {
        return $_REQUEST;
    }

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
        return Arr::set($_REQUEST, $key, $value);
    }

    static public function setIndexRequest($index, $value, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::setIndex($_REQUEST, $index, $value, $delimiter);
    }

    static public function setIndexRefRequest($index, &$value, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::setIndex($_REQUEST, $index, $value, $delimiter);
    }

    static public function getRequest($key, $else = null)
    {
        return Arr::get($_REQUEST, $key, $else);
    }

    static public function &getRefRequest($key, $else = null)
    {
        return Arr::getRef($_REQUEST, $key, $else);
    }

    static public function requiredRequest($key)
    {
        $value = static::getRequest($key);

        if (!$value) {
            throw new \Exception('Undefined value for `' . $key . '`.');
        }

        return $value;
    }

    static public function getArrayRequest($key, $else = null)
    {
        return Arr::getArray($_REQUEST, $key, $else);
    }

    static public function &getIndexRequest($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndex($_REQUEST, $index, $else, $delimiter);
    }

    static public function &getIndexRefRequest($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexRef($_REQUEST, $index, $else, $delimiter);
    }

    static public function getIndexArrayRequest($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexArray($_REQUEST, $index, $else, $delimiter);
    }

    static public function delRequest($key, ...$keys)
    {
        return Arr::del($_REQUEST, $key, ...$keys);
    }

    static public function indexDelRequest($index, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::delIndex($_REQUEST, $index, $delimiter);
    }

    static public function delAll($key, ...$keys)
    {
        Arr::del($_GET, $key, ...$keys);

        Arr::del($_POST, $key, ...$keys);

        Arr::del($_REQUEST, $key, ...$keys);

        return true;
    }
}