<?php


namespace Greg\Support\View\Compiler;

use Greg\Support\File;
use Greg\Support\Obj;
use Greg\Support\Regex\InNamespace;
use Greg\Support\View\Compiler;

class Blade extends Compiler
{
    protected $cachePath = null;

    protected $compilers = [
        'compileEchos',
    ];

    protected $echoMethods = [
        'compileRawEchos' => ['{!!', '!!}'],
        'compileContentEchos' => ['{{', '}}'],
    ];

    public function __construct($cachePath)
    {
        $this->cachePath($cachePath);

        return $this;
    }

    public function getCacheFileName($id)
    {
        return md5($id) . '.php';
    }

    public function getCacheFile($id)
    {
        return $this->cachePath() . '/' . $this->getCacheFileName($id);
    }

    public function expiredFile($file)
    {
        if (!file_exists($file)) {
            return true;
        }

        $cacheFile = $this->getCacheFile($file);

        if (!file_exists($cacheFile)) {
            return true;
        }

        return filemtime($file) !== filemtime($cacheFile);
    }

    public function save($id, $string)
    {
        $file = $this->getCacheFile($id);

        File::fixFileDirRecursive($file);

        file_put_contents($file, $string);

        return $this;
    }

    public function getCompiledFile($file)
    {
        if ($this->expiredFile($file)) {
            $this->save($file, $this->compileFile($file));
        }

        return $this->getCacheFile($file);
    }

    public function compileFile($file)
    {
        if (!file_exists($file)) {
            throw new \Exception('Blade file not found.');
        }

        return $this->compileString(file_get_contents($file));
    }

    public function compileString($string)
    {
        $result = '';

        // Here we will loop through all of the tokens returned by the Zend lexer and
        // parse each one into the corresponding valid PHP. We will then have this
        // template as the correctly rendered PHP that can be rendered natively.
        foreach (token_get_all($string) as $token) {
            $result .= is_array($token) ? $this->parseToken($token) : $token;
        }

        return $result;
    }

    /**
     * Parse the tokens from the template.
     *
     * @param  array  $token
     * @return string
     */
    protected function parseToken(array $token)
    {
        list($id, $content) = $token;

        if ($id == T_INLINE_HTML) {
            foreach ($this->compilers as $callable) {
                if (!is_callable($callable) and is_scalar($callable)) {
                    $callable = [$this, $callable];
                }

                $content = call_user_func_array($callable, [$content]);
            }
        }

        return $content;
    }

    /**
     * Compile Blade echos into valid PHP.
     *
     * @param  string  $string
     * @return string
     */
    public function compileEchos($string)
    {
        foreach (array_keys($this->getEchoMethods()) as $method) {
            $string = $this->$method($string);
        }

        return $string;
    }

    public function compileRawEchos($string)
    {
        $pattern = $this->inNamespaceRegex(...$this->echoMethods[__FUNCTION__]);

        $string = preg_replace_callback('#' . $pattern . '#i', function($matches) {
            return '<?php echo ' . $matches['captured'] . '; ?>';
        }, $string);

        return $string;
    }

    public function compileContentEchos($string)
    {
        $pattern = $this->inNamespaceRegex(...$this->echoMethods[__FUNCTION__]);

        $string = preg_replace_callback('#' . $pattern . '#i', function($matches) {
            return '<?php echo htmlspecialchars(' . $matches['captured'] . ', ENT_QUOTES); ?>';
        }, $string);

        return $string;
    }

    protected function inNamespaceRegex($start, $end = null)
    {
        $pattern = new InNamespace($start, $end ?: $start);

        $pattern->recursive(false);

        $pattern->capturedKey('captured');

        $pattern->disableInQuotes();

        $pattern->newLines(true);

        $pattern->trim(true);

        return $pattern->toString();
    }

    /**
     * Get the echo methods in the proper order for compilation.
     *
     * @return array
     */
    protected function getEchoMethods()
    {
        $echoMethods = $this->echoMethods;

        uasort($echoMethods, function($a, $b) {
            return gmp_cmp(mb_strlen($a[0]) + mb_strlen($a[1]), mb_strlen($b[0]) + mb_strlen($b[1])) * -1;
        });

        return $echoMethods;
    }

    public function cachePath($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }
}