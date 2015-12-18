<?php

namespace Greg\Html;

use Greg\Html\Head\Link;
use Greg\Html\Head\Meta;
use Greg\Html\Head\Style;
use Greg\Tool\Obj;

class Head
{
    protected $title = null;

    protected $meta = null;

    protected $link = null;

    protected $style = null;

    /**
     * @param Meta $value
     * @return Meta|$this|null
     */
    public function meta(Meta $value = null)
    {
        return Obj::fetchEmptyVar($this, $this->{__FUNCTION__}, function() { return new Meta(); }, ...func_get_args());
    }

    /**
     * @param Link $value
     * @return Link|$this|null
     */
    public function link(Link $value = null)
    {
        return Obj::fetchEmptyVar($this, $this->{__FUNCTION__}, function() { return new Link(); }, ...func_get_args());
    }

    /**
     * @param Style $value
     * @return Style|$this|null
     */
    public function style(Style $value = null)
    {
        return Obj::fetchEmptyVar($this, $this->{__FUNCTION__}, function() { return new Style(); }, ...func_get_args());
    }

    public function title($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }
}