<?php

namespace Greg\Component\Layout;

use Greg\Component\Layout;
use Greg\Event\Listener;
use Greg\Html\ElementClass;
use Greg\Html\Head;
use Greg\Http\Response;
use Greg\Support\Obj;
use Greg\Tools\Minify;

class Html extends Layout
{
    protected $htmlClass = [];

    protected $head = null;

    protected $headOpen = [];

    protected $headClose = [];

    protected $bodyClass = [];

    protected $bodyOpen = [];

    protected $bodyClose = [];

    protected $subLayout = 'layout/default';

    protected $minifyHtml = false;

    public function __construct()
    {
        $this->htmlClass(new ElementClass());

        $this->bodyClass(new ElementClass());

        $this->head(new Head());

        return $this;
    }

    public function subscribe(Listener $listener)
    {
        $listener->register([static::EVENT_AFTER_DISPATCH], $this);

        return parent::subscribe($listener);
    }

    public function layoutAfterDispatch(Response $response)
    {
        if ($this->minifyHtml()) {
            if ($response->contentType() == 'text/html') {
                $response->body(Minify::html($response->body()));
            }
        }

        return $this;
    }

    /**
     * @param ElementClass $value
     * @return ElementClass|$this|null
     */
    public function htmlClass(ElementClass $value = null)
    {
        return Obj::fetchVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    /**
     * @param Head $value
     * @return Head|$this|null
     */
    public function head(Head $value = null)
    {
        return Obj::fetchVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function headOpen($key = null, $value = null, $type = Obj::VAR_APPEND, $recursive = false, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function headClose($key = null, $value = null, $type = Obj::VAR_APPEND, $recursive = false, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    /**
     * @param ElementClass $value
     * @return ElementClass|$this|null
     */
    public function bodyClass(ElementClass $value = null)
    {
        return Obj::fetchVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function bodyOpen($key = null, $value = null, $type = Obj::VAR_APPEND, $recursive = false, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function bodyClose($key = null, $value = null, $type = Obj::VAR_APPEND, $recursive = false, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function subLayout($value = null, $type = Obj::VAR_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function minifyHtml($value = null)
    {
        return Obj::fetchBoolVar($this, $this->{__FUNCTION__}, func_get_args());
    }
}