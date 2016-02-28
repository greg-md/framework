<?php

namespace Greg\Http;

use Greg\Server\Server;
use Greg\Storage\AccessorTrait;
use Greg\Storage\ArrayAccessTrait;
use Greg\Tool\Arr;

class Request implements \ArrayAccess
{
    use AccessorTrait, ArrayAccessTrait;

    const URI_ALL = 'all';

    const URI_PATH = 'path';

    const URI_QUERY = 'query';

    const UPLOAD_ERR = [
        UPLOAD_ERR_OK => 'There is no error, the file uploaded with success.',
        UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
        UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
        UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.', // Introduced in PHP 4.3.10 and PHP 5.0.3.
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.', // Introduced in PHP 5.1.0.
        UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.', // Introduced in PHP 5.2.0.
        // PHP does not provide a way to ascertain which extension caused the file upload to stop;
        // examining the list of loaded extensions with phpinfo() may help.
    ];

    const IMAGE_MIMES = [
        // gif
        'image/gif',
        // jpg
        'image/jpg',
        'image/jpeg',
        'image/pjpeg',
        //png
        'image/png',
    ];

    public function __construct(array $params = [])
    {
        $this->storage($params);

        return $this;
    }

    public function validate()
    {
        return $this;
    }

    public function is()
    {
        return (bool)$this->accessor();
    }

    public function &all()
    {
        return $this->accessor();
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

    static public function uri()
    {
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
        list($path, $query) = array_pad(explode('?', static::uri(), 2), 2, null);

        unset($path);

        return $query;
    }

    static public function relativeUri()
    {
        $uri = static::uri();

        $baseUri = static::baseUri();

        return $baseUri !== '/' ? mb_substr($uri, mb_strlen($baseUri)) : $uri;
    }

    static public function relativeUriPath()
    {
        list($path) = explode('?', static::relativeUri(), 2);

        return $path;
    }

    static public function relativeUriQuery()
    {
        list($path, $query) = array_pad(explode('?', static::relativeUri(), 2), 2, null);

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

    static public function header($header)
    {
        $header = strtoupper($header);

        $header = str_replace('-', '_', $header);

        return Server::get('HTTP_' . $header);
    }

    // Start standard $_GET array methods

    static public function hasGet($key, ...$keys)
    {
        return Arr::hasRef($_GET, $key, ...$keys);
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
        return Arr::hasRef($_POST, $key, ...$keys);
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

    static public function onlyPost(array $keys = [])
    {
        $values = [];

        foreach($keys as $k => $key) {
            $values[is_int($k) ? $key : $k] = static::getPost($key);
        }

        return $values;
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
        return Arr::hasRef($_REQUEST, $key, ...$keys);
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

    static public function isFiles()
    {
        return (bool)$_FILES;
    }

    static public function &allFiles()
    {
        return $_FILES;
    }

    // Start standard $_FILES array methods

    static public function hasFiles($key, ...$keys)
    {
        return Arr::hasRef($_FILES, $key, ...$keys);
    }

    static public function hasIndexFiles($index, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::hasIndex($_FILES, $index, $delimiter);
    }

    static public function setFiles($key, $value)
    {
        return Arr::set($_FILES, $key, $value);
    }

    static public function setRefFiles($key, &$value)
    {
        return Arr::setRef($_FILES, $key, $value);
    }

    static public function setIndexFiles($index, $value, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::setIndex($_FILES, $index, $value, $delimiter);
    }

    static public function setIndexRefFiles($index, &$value, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::setIndexRef($_FILES, $index, $value, $delimiter);
    }

    static public function getFiles($key, $else = null)
    {
        return Arr::get($_FILES, $key, $else);
    }

    static public function &getRefFiles($key, $else = null)
    {
        return Arr::getRef($_FILES, $key, $else);
    }

    static public function getForceFiles($key, $else = null)
    {
        return Arr::getForce($_FILES, $key, $else);
    }

    static public function &getForceRefFiles($key, $else = null)
    {
        return Arr::getForceRef($_FILES, $key, $else);
    }

    static public function getArrayFiles($key, $else = null)
    {
        return Arr::getArray($_FILES, $key, $else);
    }

    static public function &getArrayRefFiles($key, $else = null)
    {
        return Arr::getArrayRef($_FILES, $key, $else);
    }

    static public function getArrayForceFiles($key, $else = null)
    {
        return Arr::getArrayForce($_FILES, $key, $else);
    }

    static public function &getArrayForceRefFiles($key, $else = null)
    {
        return Arr::getArrayForceRef($_FILES, $key, $else);
    }

    static public function getIndexFiles($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndex($_FILES, $index, $else, $delimiter);
    }

    static public function &getIndexRefFiles($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexRef($_FILES, $index, $else, $delimiter);
    }

    static public function getIndexForceFiles($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexForce($_FILES, $index, $else, $delimiter);
    }

    static public function &getIndexForceRefFiles($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexForceRef($_FILES, $index, $else, $delimiter);
    }

    static public function getIndexArrayFiles($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexArray($_FILES, $index, $else, $delimiter);
    }

    static public function &getIndexArrayRefFiles($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexArrayRef($_FILES, $index, $else, $delimiter);
    }

    static public function getIndexArrayForceFiles($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexArrayForce($_FILES, $index, $else, $delimiter);
    }

    static public function &getIndexArrayForceRefFiles($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexArrayForceRef($_FILES, $index, $else, $delimiter);
    }

    static public function requiredFiles($key)
    {
        return Arr::required($_FILES, $key);
    }

    static public function &requiredRefFiles($key)
    {
        return Arr::requiredRef($_FILES, $key);
    }

    static public function delFiles($key, ...$keys)
    {
        return Arr::del($_FILES, $key, ...$keys);
    }

    static public function indexDelFiles($index, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::delIndex($_FILES, $index, $delimiter);
    }

    // End standard $_FILES array methods

    static public function humanReadableFiles()
    {
        $_FILES = static::humanReadableData($_FILES);

        return true;
    }

    static public function humanReadableData($data)
    {
        foreach($data as &$item) {
            if (is_array(current($item))) {
                $newItem = [];

                foreach($item as $key => $value) {
                    static::addNewArrayLevel($value, $key);

                    $newItem = array_replace_recursive($newItem, $value);
                }

                $item = $newItem;
            }
        }

        unset($item);

        return $data;
    }

    static protected function addNewArrayLevel(&$array, $key)
    {
        foreach ($array as &$item) {
            if (is_array($item)) {
                static::addNewArrayLevel($item, $key);
            } else {
                $item = [$key => $item];
            }
        }

        unset($item);

        return true;
    }

    static public function getFile($name, $mimes = [])
    {
        $file = static::getFiles($name);

        if (!$file or !$file['tmp_name']) {
            return null;
        }

        static::checkFile($file, $mimes);

        return $file;
    }

    static public function getIndexFile($name, $mimes = [])
    {
        $file = static::getIndexFiles($name);

        if (!$file or !$file['tmp_name']) {
            return null;
        }

        static::checkFile($file, $mimes);

        return $file;
    }

    static public function checkFile($file, $mimes = [])
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \Exception('Upload file error: ' . static::UPLOAD_ERR[$file['error']]);
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            throw new \Exception('Possible file upload attack.');
        }

        if ($mimes and !in_array($file['type'], (array)$mimes)) {
            throw new \Exception('Wrong file type was uploaded. Valid types are: ' . implode(', ', $mimes));
        }

        return true;
    }

    static public function getImage($name, $mimes = self::IMAGE_MIMES)
    {
        return static::getFile($name, $mimes);
    }

    static public function getIndexImage($name, $mimes = self::IMAGE_MIMES)
    {
        return static::getIndexFile($name, $mimes);
    }

    static public function getPostEmail($key)
    {
        $email = static::getPost($key);

        if ($email and !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \Exception('Value for `' . $key . '` is not an email.');
        }

        return $email;
    }

    static public function delAll($key, ...$keys)
    {
        Arr::del($_GET, $key, ...$keys);

        Arr::del($_POST, $key, ...$keys);

        Arr::del($_REQUEST, $key, ...$keys);

        Arr::del($_FILES, $key, ...$keys);

        return true;
    }
}