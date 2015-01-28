<?php

namespace Greg\System;

use \Greg\Support\Image as ImageHelper;

class Image extends File
{
    public function type()
    {
        return ImageHelper::type($this->file());
    }
}