<?php

namespace Greg\Html;

use Greg\Engine\Internal;
use Greg\Html\Head\Link;
use Greg\Html\Head\Meta;
use Greg\Html\Head\Style;
use Greg\Support\Obj;

class Head
{
    use Internal;

    protected $title = null;

    protected $meta = null;

    protected $link = null;

    protected $style = null;

    public function init()
    {
        $this->meta(Meta::newInstance($this->appName()));

        $this->link(Link::newInstance($this->appName()));

        $this->style(Style::newInstance($this->appName()));

        return $this;
    }

    /**
     * @param Meta $value
     * @return Meta|$this|null
     */
    public function meta(Meta $value = null)
    {
        return Obj::fetchVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    /**
     * @param Link $value
     * @return Link|$this|null
     */
    public function link(Link $value = null)
    {
        return Obj::fetchVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    /**
     * @param Style $value
     * @return Style|$this|null
     */
    public function style(Style $value = null)
    {
        return Obj::fetchVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function title($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }
}