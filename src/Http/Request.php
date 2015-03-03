<?php

namespace Greg\Http;

use Greg\Engine\Internal;
use Greg\Engine\InternalInterface;
use Greg\Server\Info;
use Greg\Storage\ArrayAccess;
use Greg\Support\Arr;

class Request implements \ArrayAccess, InternalInterface
{
    use ArrayAccess, Internal;

    static public function &protocol()
    {
        return Info::get('SERVER_PROTOCOL');
    }

    static public function &clientHost()
    {
        return Info::get('HTTP_HOST');
    }

    static public function &serverHost()
    {
        return Info::get('SERVER_NAME');
    }

    static public function &serverAdmin()
    {
        return Info::get('SERVER_ADMIN');
    }

    static public function &secured()
    {
        return Info::get('HTTPS');
    }

    static public function &with()
    {
        return Info::get('HTTP_X_REQUESTED_WITH');
    }

    static public function &port()
    {
        return Info::get('SERVER_PORT');
    }

    static public function &agent()
    {
        return Info::get('HTTP_USER_AGENT');
    }

    static public function &ip()
    {
        return Info::get('REMOTE_ADDR');
    }

    static public function &uri()
    {
        return Info::get('REQUEST_URI');
    }

    static public function &referrer()
    {
        return Info::get('HTTP_REFERER');
    }

    static public function &modifiedSince()
    {
        return Info::get('HTTP_IF_MODIFIED_SINCE');
    }

    static public function &match()
    {
        return Info::get('HTTP_IF_NONE_MATCH');
    }

    static public function &time()
    {
        return Info::requestTime();
    }

    static public function &microTime()
    {
        return Info::requestMicroTime();
    }

    static public function ajax()
    {
        return static::with() == 'XMLHttpRequest';
    }

    static public function isRequest()
    {
        return (bool)$_REQUEST;
    }

    static public function isGet()
    {
        return (bool)$_GET;
    }

    static public function isPost()
    {
        return (bool)$_POST;
    }

    static public function &paramRequest()
    {
        return $_REQUEST;
    }

    static public function &paramGet()
    {
        return $_GET;
    }

    static public function &paramPost()
    {
        return $_POST;
    }

    static public function hasRequest($index)
    {
        return array_key_exists($index, $_REQUEST);
    }

    static public function hasGet($index)
    {
        return array_key_exists($index, $_GET);
    }

    static public function hasPost($index)
    {
        return array_key_exists($index, $_POST);
    }

    static public function &getRequest($index, $else = null)
    {
        if (static::hasRequest($index)) return $_REQUEST[$index]; return $else;
    }

    static public function &getGet($index, $else = null)
    {
        if (static::hasGet($index)) return $_GET[$index]; return $else;
    }

    static public function &getPost($index, $else = null)
    {
        if (static::hasPost($index)) return $_POST[$index]; return $else;
    }

    static public function setRequest($index, $value)
    {
        $_REQUEST[$index] = $value;

        return true;
    }

    static public function setGet($index, $value)
    {
        $_GET[$index] = $value;

        return true;
    }

    static public function setPost($index, $value)
    {
        $_POST[$index] = $value;

        return true;
    }

    static public function delRequest($index)
    {
        unset($_REQUEST[$index]);

        return true;
    }

    static public function delGet($index)
    {
        unset($_GET[$index]);

        return true;
    }

    static public function delPost($index)
    {
        unset($_POST[$index]);

        return true;
    }

    static public function indexHasRequest($index, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::indexHas($_REQUEST, $index, $delimiter);
    }

    static public function indexHasGet($index, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::indexHas($_GET, $index, $delimiter);
    }

    static public function indexHasPost($index, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::indexHas($_POST, $index, $delimiter);
    }

    static public function &indexGetRequest($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::indexGet($_REQUEST, $index, $else, $delimiter);
    }

    static public function &indexGetGet($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::indexGet($_GET, $index, $else, $delimiter);
    }

    static public function &indexGetPost($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::indexGet($_POST, $index, $else, $delimiter);
    }

    static public function indexSetRequest($index, $value, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::indexSet($_REQUEST, $index, $value, $delimiter);
    }

    static public function indexSetGet($index, $value, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::indexSet($_GET, $index, $value, $delimiter);
    }

    static public function indexSetPost($index, $value, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::indexSet($_POST, $index, $value, $delimiter);
    }

    static public function indexDelRequest($index, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::indexDel($_REQUEST, $index, $delimiter);
    }

    static public function indexDelGet($index, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::indexDel($_GET, $index, $delimiter);
    }

    static public function indexDelPost($index, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::indexDel($_POST, $index, $delimiter);
    }
}