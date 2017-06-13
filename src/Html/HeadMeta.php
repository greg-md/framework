<?php

namespace Greg\Framework\Html;

class HeadMeta
{
    private $storage = [];

    public function get(string $name): ?array
    {
        return $this->storage[$name] ?? null;
    }

    public function name(string $name, string $content)
    {
        $this->storage[$name] = [
            'name'    => $name,
            'content' => $this->cleanupContent($content),
        ];

        return $this;
    }

    public function property(string $name, string $content)
    {
        $this->storage[$name] = [
            'property' => $name,
            'content'  => $this->cleanupContent($content),
        ];

        return $this;
    }

    public function httpEquiv(string $name, string $content)
    {
        $this->storage[$name] = [
            'http-equiv' => $name,
            'content'    => $this->cleanupContent($content),
        ];

        return $this;
    }

    public function charset(string $charset)
    {
        $this->storage['charset'] = [
            'charset' => $this->cleanupContent($charset),
        ];

        return $this;
    }

    public function refresh(int $timeout = 0, string $url = null)
    {
        $content = [
            $timeout,
        ];

        if ($url) {
            $content[] = 'url=' . $url;
        }

        return $this->httpEquiv('refresh', implode('; ', $content));
    }

    public function author($name)
    {
        return $this->name('author', $name);
    }

    public function description($name)
    {
        return $this->name('description', $name);
    }

    public function generator($name)
    {
        return $this->name('generator', $name);
    }

    public function keywords($name)
    {
        return $this->name('keywords', $name);
    }

    public function viewport($name)
    {
        return $this->name('viewport', $name);
    }

    public function toObjects(): array
    {
        $items = [];

        foreach ($this->storage as $key => $attr) {
            $items[$key] = new HtmlElement('meta', $attr);
        }

        return $items;
    }

    public function toString(): string
    {
        return implode("\n", $this->toObjects());
    }

    public function __toString()
    {
        return $this->toString();
    }

    private function cleanupContent($content): string
    {
        return HtmlElement::cleanupAttribute(trim(strip_tags($content)));
    }
}
