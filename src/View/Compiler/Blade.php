<?php

namespace Greg\View\Compiler;

use Greg\Engine\InternalTrait;

class Blade extends \Greg\Support\View\Compiler\Blade
{
    use InternalTrait;

    static public function create($appName, $cachePath)
    {
        return static::newInstanceRef($appName, $cachePath);
    }

    public function init()
    {
        $this->statements([
            'partialLoop' => 'compilePartialLoop',
            'route' => 'compileRoute',
            'translate' => 'compileTranslate',
            'translateKey' => 'compileTranslateKey',
            'translateRaw' => 'compileTranslateRaw',
            'translateRawKey' => 'compileTranslateRawKey',
            'fetchNameIfExists' => 'compileFetchNameIfExists',
            'baseUrl' => 'compileBaseUrl',
        ]);

        $this->emptyStatements([
            'content' => 'compileContent',
            'language' => 'compileLanguage',
        ]);

        return $this;
    }

    public function compilePartialLoop($expr)
    {
        return $this->compileRawEcho('$this->partialLoop(' . $expr . ')');
    }

    public function compileRoute($expr)
    {
        return $this->compileContentEcho('$this->app()->router()->fetchRoute(' . $expr . ')');
    }

    public function compileContent()
    {
        return $this->compileRawEcho('$this->content()');
    }

    public function compileTranslate($expr)
    {
        return $this->compileContentEcho('$this->app()->translator()->translate(' . $expr . ')');
    }

    public function compileTranslateKey($expr)
    {
        return $this->compileContentEcho('$this->app()->translator()->translateKey(' . $expr . ')');
    }

    public function compileTranslateRaw($expr)
    {
        return $this->compileRawEcho('$this->app()->translator()->translate(' . $expr . ')');
    }

    public function compileTranslateRawKey($expr)
    {
        return $this->compileRawEcho('$this->app()->translator()->translateKey(' . $expr . ')');
    }

    public function compileLanguage()
    {
        return $this->compileContentEcho('$this->app()->translator()->language()');
    }

    public function compileFetchNameIfExists($expr)
    {
        return $this->compileRawEcho('$this->fetchNameIfExists(' . $expr . ')');
    }

    public function compileBaseUrl($expr)
    {
        return $this->compileContentEcho('\Greg\Support\Tool\Url::base(' . $expr . ')');
    }
}