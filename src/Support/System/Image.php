<?php

namespace Greg\Support\System;

class Image extends File
{
    public function type()
    {
        return \Greg\Support\Image::type($this->file());
    }
}