<?php

namespace Greg\Application\View\Compiler;

use Greg\Application\Engine\InternalTrait;

class Blade extends \Greg\View\Compiler\Blade
{
    use InternalTrait;

    static public function create($appName, $cachePath)
    {
        return static::newInstanceRef($appName, $cachePath);
    }

    public function init()
    {
        $this->statements([
            'action' => 'compileAction',
            'partial' => 'compilePartial',
            'partialLoop' => 'compilePartialLoop',
            'route' => 'compileRoute',
            'translate' => 'compileTranslate',
            't' => 'compileTranslate',
            'translateKey' => 'compileTranslateKey',
            'tk' => 'compileTranslateKey',
            'translateRaw' => 'compileTranslateRaw',
            'tr' => 'compileTranslateRaw',
            'translateRawKey' => 'compileTranslateRawKey',
            'trk' => 'compileTranslateRawKey',
            'fetchNameIfExists' => 'compileFetchNameIfExists',
            'baseUrl' => 'compileBaseUrl',
            'fixUrl' => 'compileFixUrl',
            'fullUrl' => 'compileFullUrl',
        ]);

        $this->emptyStatements([
            'content' => 'compileContent',
            'language' => 'compileLanguage',
        ]);

        return $this;
    }

    public function compileAction($expr)
    {
        return $this->compileRawEcho('$this->app()->action(' . $expr . ')');
    }

    public function compilePartial($expr)
    {
        return $this->compileRawEcho('$this->partial(' . $expr . ')');
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
        return $this->compileContentEcho('\Greg\Tool\Url::base(' . $expr . ')');
    }

    public function compileFixUrl($expr)
    {
        return $this->compileContentEcho('\Greg\Tool\Url::fix(' . $expr . ')');
    }

    public function compileFullUrl($expr)
    {
        return $this->compileContentEcho('\Greg\Tool\Url::full(' . $expr . ')');
    }
}