<?php


namespace Greg\View\Compiler;

use Greg\Support\Engine\InternalTrait;
use Greg\View\CompilerInterface;

class Blade implements CompilerInterface
{
    use InternalTrait;

    public function __construct()
    {

    }

    static public function create($appName)
    {
        return static::newInstanceRef($appName);
    }

    public function fetchFile($file)
    {
        dd('lol');
    }

    public function fetchString($string)
    {

    }
}