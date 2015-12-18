<?php

namespace Greg\Console;

use Greg\Tool\Obj;

class Execute
{
    protected $command = null;

    //protected $inBackground = false;

    public function __construct($command/*, $inBackground = null*/)
    {
        $this->command($command);

        /*
        if ($inBackground !== null) {
            $this->inBackground($inBackground);
        }
        */

        return $this;
    }

    public function run()
    {
        exec($this->command(), $out, $return);

        return $out;
    }

    public function runInBackground()
    {
        exec($this->command() . ' > /dev/null &', $out, $return);

        return $out;
    }

    public function command($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    /*
    public function inBackground($value = null)
    {
        return Obj::fetchBoolVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }
    */
}