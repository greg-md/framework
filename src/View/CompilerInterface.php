<?php

namespace Greg\View;

interface CompilerInterface
{
    public function fetchFile($file);

    public function fetchString($string);
}