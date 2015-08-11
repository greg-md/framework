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
        ]);

        return $this;
    }

    public function compilePartialLoop($expr)
    {
        return '<?php echo $this->partialLoop(' . $expr . '); ?>';
    }

    public function compileRoute($expr)
    {
        return '<?php echo $this->app()->router()->fetchRoute(' . $expr . '); ?>';
    }
}