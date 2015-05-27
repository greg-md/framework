<?php

namespace Greg\Component\Layout;

use Greg\Application\Runner;
use Greg\Component\Layout;
use Greg\Engine\Internal;
use Greg\Event\Listener;
use Greg\Event\SubscriberInterface;
use Greg\Event\SubscriberTrait;
use Greg\Html\ElementClass;
use Greg\Html\Head;
use Greg\Html\Script;
use Greg\Http\Response;
use Greg\Support\Obj;
use Greg\Tool\Minify;

class Html implements SubscriberInterface
{
    use SubscriberTrait, Internal;

    protected $htmlClass = [];

    protected $head = null;

    protected $headOpen = [];

    protected $headClose = [];

    protected $bodyClass = [];

    protected $bodyOpen = [];

    protected $bodyClose = [];

    protected $script = null;

    protected $minifyHtml = false;

    public function __bind()
    {
        $this->htmlClass(ElementClass::newInstance($this->appName()));

        $this->head(Head::newInstance($this->appName()));

        $this->bodyClass(ElementClass::newInstance($this->appName()));

        $this->script(Script::newInstance($this->appName()));

        return $this;
    }

    public function subscribe(Listener $listener)
    {
        $listener->register([
            Runner::EVENT_DISPATCHED
        ], $this);

        return $this;
    }

    public function appDispatched(&$response)
    {
        if ($this->minifyHtml()) {
            if (is_string($response)) {
                $response = Minify::html($response);
            } elseif ($response instanceof Response and $response->contentType() == 'text/html') {
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
        return Obj::fetchVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    /**
     * @param Head $value
     * @return Head|$this|null
     */
    public function head(Head $value = null)
    {
        return Obj::fetchVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function headOpen($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function headClose($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    /**
     * @param ElementClass $value
     * @return ElementClass|$this|null
     */
    public function bodyClass(ElementClass $value = null)
    {
        return Obj::fetchVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function &bodyOpen($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function &bodyClose($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    /**
     * @param Script $value
     * @return Script|$this|null
     */
    public function script(Script $value = null)
    {
        return Obj::fetchVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function minifyHtml($value = null)
    {
        return Obj::fetchBoolVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }
}