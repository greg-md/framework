<?php

namespace Greg\Http;

use Greg\Support\Engine\InternalTrait;
use Greg\Support\Obj;

class Response extends \Greg\Support\Http\Response
{
    use InternalTrait;

    protected $contentType = 'text/html';

    protected $charset = 'UTF-8';

    protected $location = null;

    protected $code = null;

    protected $content = null;

    protected $callbacks = [];

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

    public function route($name, array $params = [], $code = null)
    {
        $this->location($this->app()->router()->fetch($name, $params));

        if ($code !== null) {
            $this->code($code);
        }

        return $this;
    }

    public function with(callable $callable)
    {
        $this->callbacks()[] = $callable;

        return $this;
    }

    public function json($data)
    {
        $this->contentType('application/json');

        $this->content(json_encode($data));

        return $this;
    }

    public function send()
    {
        if ($callbacks = $this->callbacks()) {
            foreach($callbacks as $callback) {
                $this->app()->binder()->callWith($callback, $this);
            }
        }

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
            $this->sendContentType(implode('; ', $contentType));
        }

        if ($code = $this->code()) {
            $this->sendCode($code);
        }

        if ($location = $this->location()) {
            $this->sendRedirect($location);
        }

        echo $this->content();

        return $this;
    }

    public function isHtml()
    {
        return $this->contentType() == 'text/html';
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

    protected function &callbacks($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function __toString()
    {
        return $this->content();
    }
}