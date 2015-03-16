<?php

namespace Greg\Application;

use Greg\Engine\Internal;
use Greg\Http\Request;
use Greg\Support\Obj;
use Greg\View\Viewer;

abstract class Controller
{
    use Internal;

    protected $name = null;

    protected $request = null;

    protected $view = null;

    public function __construct($name, Request $request, Viewer $view)
    {
        $this->name($name);

        $this->request($request);

        $this->view($view);

        return $this;
    }

    static public function create($appName, $name, Request $request, Viewer $view)
    {
        return static::newInstanceRef($appName, $name, $request, $view);
    }

    public function init()
    {
        $this->view()->controllers($this->name(), $this);

        return $this;
    }

    public function name($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    /**
     * @param Request $value
     * @return Request|$this|null
     */
    public function request(Request $value = null)
    {
        return Obj::fetchVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    /**
     * @param Viewer $value
     * @return Viewer|$this|null
     */
    public function view(Viewer $value = null)
    {
        return Obj::fetchVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }
}