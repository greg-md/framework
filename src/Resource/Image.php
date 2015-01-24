<?php

namespace Greg\Resource;

use \Greg\Support\Image as ImageHelper;

class Image extends File
{
    public function type()
    {
        return ImageHelper::type($this->file());
    }
}