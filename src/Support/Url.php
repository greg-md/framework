<?php

namespace Greg\Support;

use Greg\Http\Request;

class Url
{
    const UA = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';

    static public function is($url)
    {
        return preg_match('#^(?:https?\:)?//#i', $url);
    }

    static public function full($url = '/')
    {
        if (static::is($url)) {
            return $url;
        }

        return static::fix(Request::clientHost() . $url, Request::isSecured());
    }

    static public function fix($url, $secured = false)
    {
        if (static::is($url)) {
            return $url;
        }

        return ($secured ? 'https' : 'http') . '://' . $url;
    }

    static public function host($url, $stripWWW = true)
    {
        $url = Url::fix($url);

        $host = parse_url($url, PHP_URL_HOST);

        if ($stripWWW and substr($host, 0, 4) == 'www.') {
            $host = substr($host, 4);
        }

        return $host;
    }

    static public function home($url)
    {
        $url = Url::fix($url);

        $info = parse_url($url);

        return $info['scheme'] . '://' . $info['host'];
    }

    static public function init($url)
    {
        $ch = curl_init(static::fix($url));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_USERAGENT, static::UA);

        return $ch;
    }

    static public function effective($url)
    {
        $ch = static::init($url);

        curl_exec($ch);

        return curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    }

    static public function contents($url)
    {
        $ch = static::init($url);

        return curl_exec($ch);
    }

    static public function transform($string, $type = Str::SPINAL_CASE)
    {
        $string = Str::replaceAccents($string);

        $replacement = array(
            'а' => 'a', 'А' => 'A',
            'б' => 'b', 'Б' => 'B',
            'в' => 'v', 'В' => 'V',
            'г' => 'g', 'Г' => 'G',
            'д' => 'd', 'Д' => 'D',
            'е' => 'e', 'Е' => 'E',
            'ё' => 'yo', 'Ё' => 'YO',
            'ж' => 'j', 'Ж' => 'J',
            'з' => 'z', 'З' => 'Z',
            'и' => 'i', 'И' => 'I',
            'й' => 'i', 'Й' => 'I',
            'к' => 'k', 'К' => 'K',
            'л' => 'l', 'Л' => 'L',
            'м' => 'm', 'М' => 'M',
            'н' => 'n', 'Н' => 'N',
            'о' => 'o', 'О' => 'O',
            'п' => 'p', 'П' => 'P',
            'р' => 'r', 'Р' => 'R',
            'с' => 's', 'С' => 'S',
            'т' => 't', 'Т' => 'T',
            'у' => 'u', 'У' => 'U',
            'ф' => 'f', 'Ф' => 'F',
            'х' => 'h', 'Х' => 'H',
            'ц' => 'c', 'Ц' => 'C',
            'ч' => 'ch', 'Ч' => 'CH',
            'ш' => 'sh', 'Ш' => 'SH',
            'щ' => 'shi', 'Щ' => 'SHI',
            'ъ' => 'i', 'Ъ' => 'I',
            'ы' => 'y', 'Ы' => 'Y',
            'ь' => 'i', 'Ь' => 'I',
            'э' => 'e', 'Э' => 'E',
            'ю' => 'yu', 'Ю' => 'YU',
            'я' => 'ya', 'Я' => 'YA',
            'ă' => 'a', 'â' => 'a', 'î' => 'i', 'Ă' => 'A', 'Â' => 'A', 'Î' => 'I',
            'ş' => 's', 'ţ' => 't',
            'ț' => 't', 'ș' => 's', 'Ș' => 's', 'Ț' => 't',
        );

        $string = strtr($string, $replacement);

        if ($type) {
            $string = Str::$type($string);
        }

        return $string;
    }
}