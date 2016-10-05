<?php

namespace Greg\View\Compiler;

use Greg\Support\Arr;
use Greg\Support\File;
use Greg\Support\InternalTrait;
use Greg\Support\Regex\InNamespaceRegex;

class BladeCompiler implements CompilerInterface
{
    use InternalTrait;

    protected $compilationPath = null;

    protected $compilers = [
        'compileStatements',
        'compileComments',
        'compileRawEchos',
        'compileContentEchos',
    ];

    protected $statements = [
        'if'         => 'compileIf',
        'elseif'     => 'compileElseIf',
        'unless'     => 'compileUnless',
        'elseunless' => 'compileElseUnless',
        'for'        => 'compileFor',
        'foreach'    => 'compileForeach',
        'while'      => 'compileWhile',

        'switch' => 'compileSwitch',
        'case'   => 'compileCase',
    ];

    protected $emptyStatements = [
        'endif'      => 'compileEndIf',
        'endunless'  => 'compileEndUnless',
        'endfor'     => 'compileEndFor',
        'endforeach' => 'compileEndForeach',
        'endwhile'   => 'compileEndWhile',
        'forelse'    => 'compileForelse',
        'endforelse' => 'compileEndForelse',

        'default'   => 'compileDefault',
        'break'     => 'compileBreak',
        'endswitch' => 'compileEndSwitch',

        'else' => 'compileElse',
        'stop' => 'compileStop',
    ];

    public function __construct($compilationPath)
    {
        $this->setCompilationPath($compilationPath);

        return $this;
    }

    public function getCompilationFileName($id)
    {
        return md5($id) . '.php';
    }

    public function getCompilationFile($id)
    {
        return $this->getCompilationPath() . DIRECTORY_SEPARATOR . $this->getCompilationFileName($id);
    }

    public function expiredFile($file)
    {
        if (!file_exists($file)) {
            return true;
        }

        $compilationFile = $this->getCompilationFile($file);

        if (!file_exists($compilationFile)) {
            return true;
        }

        return filemtime($file) > filemtime($compilationFile);
    }

    public function save($id, $string)
    {
        $file = $this->getCompilationFile($id);

        File::fixFileDirRecursive($file);

        file_put_contents($file, $string);

        return $this;
    }

    public function getCompiledFile($file)
    {
        if ($this->expiredFile($file)) {
            $this->save($file, $this->compileFile($file));
        }

        return $this->getCompilationFile($file);
    }

    public function compileFile($file)
    {
        if (!file_exists($file)) {
            throw new \Exception('Blade file `' . $file . '` not found.');
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
     * @param array $token
     *
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

                $content = $this->callCallable($callable, $content);
            }
        }

        return $content;
    }

    public function compileComments($string)
    {
        return $this->inNamespaceRegex('{{--', '--}}')->replaceCallback(function ($matches) {
            return '<?php /* ' . $matches['captured'] . ' */ ?>';
        }, $string, 'i');
    }

    public function compileRawEchos($string)
    {
        return $this->inNamespaceRegex('{!!', '!!}')->replaceCallback(function ($matches) {
            return $this->compileRawEcho($matches['captured']);
        }, $string, 'i');
    }

    public function compileContentEchos($string)
    {
        return $this->inNamespaceRegex('{{', '}}')->replaceCallback(function ($matches) {
            return $this->compileContentEcho($matches['captured']);
        }, $string, 'i');
    }

    public function compileRawEcho($string)
    {
        return '<?php echo ' . $string . '; ?>';
    }

    public function compileContentEcho($string)
    {
        return '<?php echo htmlspecialchars(' . $string . ', ENT_QUOTES); ?>';
    }

    public function compileStatements($value)
    {
        $statements = array_map('preg_quote', array_keys($this->statements));

        $statements = implode('|', $statements);

        $emptyStatements = array_map('preg_quote', array_keys($this->emptyStatements));

        $emptyStatements = implode('|', $emptyStatements);

        $exprNamespace = $this->inNamespaceRegex('(', ')');

        $exprNamespace->recursive(true);

        $exprNamespace->setRecursiveGroup('recursive');

        $exprRegex = '[\s\t]*(?\'recursive\'' . $exprNamespace . ')';

        $extendsRegex = '(?\'extends\'->[\s\t]*[a-z0-9_]+[\s\t]*\g\'recursive\'(\g\'extends\')?)?';

        $pattern = '@(?:(?\'statement\'' . $statements . ')' . $exprRegex . '|(?\'empty\'' . $emptyStatements . ')\b;?)' . $extendsRegex;

        return preg_replace_callback('#' . $pattern . '#i', function ($matches) {
            if ($statement = Arr::get($matches, 'empty')) {
                $callable = $this->emptyStatements[$statement];

                $args = [];
            } else {
                $callable = $this->statements[$matches['statement']];

                $args = [$matches['captured']];
            }

            if ($extends = Arr::get($matches, 'extends')) {
                $args[] = $extends;
            }

            if (!is_callable($callable) and is_scalar($callable)) {
                $callable = [$this, $callable];
            }

            return $this->callCallable($callable, ...$args);
        }, $value);
    }

    public function compileIf($expr)
    {
        return '<?php if(' . $expr . '): ?>';
    }

    public function compileElseIf($expr)
    {
        return '<?php elseif(' . $expr . '): ?>';
    }

    public function compileEndIf()
    {
        return '<?php endif; ?>';
    }

    public function compileUnless($expr)
    {
        return '<?php if(!(' . $expr . ')): ?>';
    }

    public function compileElseUnless($expr)
    {
        return '<?php elseif(!(' . $expr . ')): ?>';
    }

    public function compileEndUnless()
    {
        return '<?php endif; ?>';
    }

    public function compileElse()
    {
        return '<?php else: ?>';
    }

    public function compileFor($expr)
    {
        return '<?php for(' . $expr . '): ?>';
    }

    public function compileEndFor()
    {
        return '<?php endfor; ?>';
    }

    protected $foreachK = 0;

    public function compileForeach($expr)
    {
        ++$this->foreachK;

        $var = '$___foreachEmpty' . $this->foreachK;

        return '<?php ' . $var . ' = true; foreach(' . $expr . '): ' . $var . ' = false; ?>';
    }

    public function compileForelse()
    {
        $var = '$___foreachEmpty' . $this->foreachK;

        --$this->foreachK;

        return '<?php endforeach; if(' . $var . '): ?>';
    }

    public function compileEndForeach()
    {
        --$this->foreachK;

        return '<?php endforeach; ?>';
    }

    public function compileEndForelse()
    {
        return '<?php endif; ?>';
    }

    public function compileWhile($expr)
    {
        return '<?php while(' . $expr . '): ?>';
    }

    public function compileEndWhile()
    {
        return '<?php endwhile; ?>';
    }

    public function compileStop()
    {
        return '<?php return; ?>';
    }

    public function compileSwitch($expr)
    {
        return '<?php switch(' . $expr . '): case uniqid(null, true): ?>';
    }

    public function compileCase($expr)
    {
        return '<?php break; case ' . $expr . ': ?>';
    }

    public function compileBreak()
    {
        return '<?php break; ?>';
    }

    public function compileDefault()
    {
        return '<?php break; default: ?>';
    }

    public function compileEndSwitch()
    {
        return '<?php endswitch; ?>';
    }

    protected function inNamespaceRegex($start, $end = null)
    {
        $pattern = new InNamespaceRegex($start, $end ?: $start);

        $pattern->recursive(false);

        $pattern->setCapturedKey('captured');

        $pattern->disableInQuotes();

        $pattern->newLines();

        $pattern->trim();

        return $pattern;
    }

    public function setCompilationPath($path)
    {
        $this->compilationPath = (string) $path;

        return $this;
    }

    public function getCompilationPath()
    {
        return $this->compilationPath;
    }
}
