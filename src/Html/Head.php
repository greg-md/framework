<?php

namespace Greg\Html;

use Greg\Engine\Internal;
use Greg\Html\Head\Link;
use Greg\Html\Head\Meta;
use Greg\Support\Obj;

class Head
{
    use Internal;

    protected $title = null;

    protected $link = null;

    protected $meta = null;

    public function __construct()
    {
        $this->link(new Link());

        $this->meta(new Meta());

        return $this;
    }

    /**
     * @param Link $value
     * @return Link|$this|null
     */
    public function link(Link $value = null)
    {
        return Obj::fetchVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    /**
     * @param Meta $value
     * @return Meta|$this|null
     */
    public function meta(Meta $value = null)
    {
        return Obj::fetchVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function title($value = null, $type = Obj::VAR_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, func_get_args());
    }
}