<?php

namespace Greg\Mailer;

use Greg\Support\Engine\Internal;
use Greg\Support\Obj;

class Mail
{
    use Internal;

    protected $subject = null;

    protected $body = [];

    protected $from = [];

    protected $to = [];

    protected $replyTo = [];

    protected $cc = [];

    protected $bcc = [];

    protected $mimeVersion = '1.0';

    protected $charset = 'utf-8'; // iso-8859-1

    public function __construct($subject = null, array $body = [], array $to = [])
    {
        if ($subject !== null) {
            $this->subject($subject);
        }

        $this->body($body);

        $this->to($body);

        return $this;
    }

    static public function create($appName, $subject = null, array $body = [], array $to = [])
    {
        return static::newInstanceRef($appName, $subject, $body, $to);
    }

    public function send(TransporterInterface $transporter = null)
    {
        if (!$transporter) {
            $transporter = new Transporter\Mail();
        }

        $transporter->send($this);
    }

    public function from($email = null, $name = null)
    {
        if (func_num_args()) {
            $this->from = [$email, $name];

            return $this;
        }

        return $this->from;
    }

    public function replyTo($email = null, $name = null)
    {
        if (func_num_args()) {
            $this->replyTo = [$email, $name];

            return $this;
        }

        return $this->replyTo;
    }

    public function plain($body)
    {
        return $this->body('text/plain', $body);
    }

    public function html($body)
    {
        return $this->body('text/html', $body);
    }

    public function toMany($emails)
    {
        return $this->to([$emails]);
    }

    public function subject($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function body($mime = null, $body = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function to($email = null, $name = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function cc($email = null, $name = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function bcc($email = null, $name = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function mimeVersion($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function charset($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }
}