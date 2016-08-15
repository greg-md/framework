<?php

namespace Greg\Html;

use Greg\Storage\AccessorTrait;

class HeadMeta
{
    use AccessorTrait;

    public function getName($name)
    {
        return $this->getFromStorage($name);
    }

    public function setName($name, $content)
    {
        $this->setToStorage($name, [
            'name' => $name,
            'content' => HtmlElement::clearAttrValue($content),
        ]);
    }

    public function getProperty($name)
    {
        return $this->getFromStorage($name);
    }

    public function setProperty($name, $content)
    {
        $this->setToStorage($name, [
            'property' => $name,
            'content' => HtmlElement::clearAttrValue($content),
        ]);
    }

    public function getHttpEquiv($name)
    {
        return $this->getFromStorage($name);
    }

    public function setHttpEquiv($name, $content)
    {
        $this->setToStorage($name, [
            'http-equiv' => $name,
            'content' => HtmlElement::clearAttrValue($content),
        ]);
    }

    public function getCharset()
    {
        return $this->getFromStorage('charset');
    }

    public function setCharset($charset)
    {
        $this->setToStorage('charset', [
            'charset' => HtmlElement::clearAttrValue($charset),
        ]);
    }

    public function getRefresh()
    {
        return $this->getHttpEquiv('refresh');
    }

    public function setRefresh($timeout = 0, $url = null)
    {
        $content = [
            $timeout,
        ];

        if ($url) {
            $content[] = 'url=' . $url;
        }

        return $this->setHttpEquiv('refresh', implode('; ', $content));
    }

    public function getAuthor()
    {
        return $this->getName('author');
    }

    public function setAuthor($name)
    {
        return $this->setName('author', $name);
    }

    public function getDescription()
    {
        return $this->getName('description');
    }

    public function setDescription($name)
    {
        return $this->setName('description', $name);
    }

    public function getGenerator()
    {
        return $this->getName('generator');
    }

    public function setGenerator($name)
    {
        return $this->setName('generator', $name);
    }

    public function getKeywords()
    {
        return $this->getName('keywords');
    }

    public function setKeywords($name)
    {
        return $this->setName('keywords', $name);
    }

    public function getViewPort()
    {
        return $this->getName('viewport');
    }

    public function setViewPort($name)
    {
        return $this->setName('viewport', $name);
    }

    public function toObjects()
    {
        $items = [];

        foreach($this->getStorage() as $id => $attr) {
            $items[$id] = new HtmlElement('meta', $attr);
        }

        return $items;
    }

    public function toString()
    {
        return implode("\n", $this->toObjects());
    }

    public function __toString()
    {
        return $this->toString();
    }
}