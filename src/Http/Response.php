<?php

namespace Greg\Http;

use Greg\Support\Engine\Internal;
use Greg\Support\Obj;

class Response extends \Greg\Support\Http\Response
{
    use Internal;

    protected $contentType = 'text/html';

    protected $charset = 'UTF-8';

    protected $location = null;

    protected $code = null;

    protected $content = null;

    public function __construct($content = null, $contentType = null)
    {
        if ($content !== null) {
            $this->content($content);
        }

        if ($contentType !== null) {
            $this->contentType($contentType);
        }

        return $this;
    }

    /**
     * @param $appName
     * @param null $content
     * @param null $contentType
     * @return self
     * @throws \Exception
     */
    static public function create($appName, $content = null, $contentType = null)
    {
        return static::newInstanceRef($appName, $content, $contentType);
    }

    public function send()
    {
        $contentType = [];

        $type = $this->contentType();
        if ($type) {
            $contentType[] = $type;
        }

        $charset = $this->charset();
        if ($charset) {
            $contentType[] = 'charset=' . $charset;
        }

        if ($contentType) {
            $this->setContentType(implode('; ', $contentType));
        }

        if ($code = $this->code()) {
            $this->setCode($code);
        }

        if ($location = $this->location()) {
            $this->redirect($location, null, false);
        }

        echo $this->content();

        return $this;
    }

    public function contentType($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function charset($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function location($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function code($value = null)
    {
        return Obj::fetchIntVar($this, $this->{__FUNCTION__}, true, ...func_get_args());
    }

    public function content($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function route($name, array $params = [], $code = null)
    {
        $this->location($this->app()->router()->fetch($name, $params));

        if ($code !== null) {
            $this->code($code);
        }

        return $this;
    }

    public function __toString()
    {
        return $this->content();
    }
}