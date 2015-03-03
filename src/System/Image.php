<?php

namespace Greg\System;

class Image extends File
{
    public function type()
    {
        return \Greg\Support\Image::type($this->file());
    }
}