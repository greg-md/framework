<?php

namespace Greg\Tools;

class Minify
{
    static public function html($html)
    {
        return preg_replace('%(?>[^\S]\s*|\s{2,})(?=(?:(?:[^<]++|<(?!/?(?:textarea|pre)\b))*+)(?:<(?>textarea|pre)\b|\z))%ix', ' ', $html);
    }
}