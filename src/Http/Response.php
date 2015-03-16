<?php

namespace Greg\Http;

use Greg\Engine\Internal;
use Greg\Support\Arr;
use Greg\Support\Obj;
use Greg\Support\Type;

class Response
{
    use Internal;

    protected $contentType = 'text/html';

    protected $charset = 'UTF-8';

    protected $body = null;

    public function __construct($body = null)
    {
        $this->body($body);

        return $this;
    }

    static public function create($appName, $body = null)
    {
        return static::newInstanceRef($appName, $body);
    }

    public function send()
    {
        $contentType = [];

        $type = $this->contentType();
        if ($type) {
            $contentType[] = $type;
        }

        $charset = $this->charset();
        if ($charset) {
            $contentType[] = 'charset=' . $charset;
        }

        if ($contentType) {
            $this->setContentType(implode('; ', $contentType));
        }

        echo $this->body();

        return $this;
    }

    public function contentType($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function charset($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function body($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    const CODES = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',            // RFC2518
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',          // RFC4918
        208 => 'Already Reported',      // RFC5842
        226 => 'IM Used',               // RFC3229
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Reserved',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',    // RFC7238
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',                                               // RFC2324
        422 => 'Unprocessable Entity',                                        // RFC4918
        423 => 'Locked',                                                      // RFC4918
        424 => 'Failed Dependency',                                           // RFC4918
        425 => 'Reserved for WebDAV advanced collections expired proposal',   // RFC2817
        426 => 'Upgrade Required',                                            // RFC2817
        428 => 'Precondition Required',                                       // RFC6585
        429 => 'Too Many Requests',                                           // RFC6585
        431 => 'Request Header Fields Too Large',                             // RFC6585
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates (Experimental)',                      // RFC2295
        507 => 'Insufficient Storage',                                        // RFC4918
        508 => 'Loop Detected',                                               // RFC5842
        510 => 'Not Extended',                                                // RFC2774
        511 => 'Network Authentication Required',                             // RFC6585
    ];

    static public function code($code)
    {
        if (Type::isNaturalNumber($code) and Arr::has($codes = static::CODES, $code)) {
            $code .= ' ' . $codes[$code];
        }

        header('HTTP/1.1 ' . $code);

        return true;
    }

    static public function redirect($url = '/', $code = null, $die = true)
    {
        if ($code !== null) {
            static::code($code);
        }

        if (!$url) {
            $url = '/';
        }

        header('Location: ' . $url, false, $code);

        $die && die;

        return true;
    }

    static public function refresh($die = true)
    {
        return static::redirect(Request::uri(), null, $die);
    }

    static public function referrerRedirect($die = true)
    {
        return static::redirect(Request::referrer(), null, $die);
    }

    static public function json($param = [], $die = true)
    {
        static::setContentType('application/json');

        echo json_encode($param);

        $die && die;

        return true;
    }

    static public function html($html, $die = true)
    {
        static::setContentType('text/html');

        echo $html;

        $die && die;

        return true;
    }

    static public function text($text, $die = true)
    {
        static::setContentType('text/plain');

        echo $text;

        $die && die;

        return true;
    }

    static public function jpeg($image, $quality = 75, $die = true)
    {
        static::setContentType('image/jpeg');

        imagejpeg($image, null, $quality);

        $die && die;

        return true;
    }

    static public function gif($image, $die = true)
    {
        static::setContentType('image/gif');

        imagegif($image);

        $die && die;

        return true;
    }

    static public function png($image, $die = true)
    {
        static::setContentType('image/png');

        imagepng($image);

        $die && die;

        return true;
    }

    static public function setContentType($type)
    {
        header('Content-Type: ' . $type);

        return true;
    }

    static public function doConditionalGet($timestamp, $maxAge = 0, $die = true)
    {
        if (!Type::isNaturalNumber($timestamp)) {
            $timestamp = strtotime($timestamp);
        }

        $modifiedSince = Request::modifiedSince();

        if ($maxAge > 0) {
            $serverTime = Request::time();

            if ($modifiedSince) {
                $modifiedSinceTime = new \DateTime($modifiedSince, new \DateTimeZone('UTC'));

                $modifiedSinceTime = strtotime($modifiedSinceTime->format('Y-m-d H:i:s'));

                if ($modifiedSinceTime < $serverTime - $maxAge) {
                    $timestamp = $serverTime;
                } elseif ($timestamp < $modifiedSinceTime) {
                    $timestamp = $modifiedSinceTime;
                }
            } else {
                $timestamp = $serverTime;
            }
        }

        $lastModified = substr(date('r', $timestamp), 0, -5) . 'GMT';

        $eTag = '"' . md5($lastModified) . '"';

        // Send the headers
        header('Last-Modified: ' . $lastModified);

        header('ETag: ' . $eTag);

        $match = Request::match();

        // See if the client has provided the required headers
        if (!$modifiedSince && !$match) {
            return false;
        }

        // At least one of the headers is there - check them
        if ($match && $match != $eTag) {
            return false; // eTag is there but doesn't match
        }

        if ($modifiedSince && $modifiedSince != $lastModified) {
            return false; // if-modified-since is there but doesn't match
        }

        // Nothing has changed since their last request - serve a 304
        static::code(304);

        $die && die;

        return true;
    }
}