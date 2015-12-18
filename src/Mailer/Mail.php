<?php

namespace Greg\Mailer;

use Greg\Tool\Obj;

class Mail
{
    protected $subject = null;

    protected $body = [];

    protected $from = [];

    protected $toStorage = [];

    protected $replyTo = [];

    protected $ccStorage = [];

    protected $bccStorage = [];

    protected $mimeVersion = '1.0';

    protected $charset = 'utf-8'; // iso-8859-1

    public function __construct($subject = null, array $body = [], $to = null)
    {
        if ($subject !== null) {
            $this->subject($subject);
        }

        $this->body($body);

        if ($to) {
            $this->to($to);
        }

        return $this;
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
        return $this->body('text/plain', is_array($body) ? implode("\n", $body) : $body);
    }

    public function html($body)
    {
        return $this->body('text/html', is_array($body) ? implode('<br />', $body) : $body);
    }

    public function subject($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function body($mime = null, $body = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function to($email = null, $name = null)
    {
        return func_num_args() ? $this->toStorage(is_array($email) ? [$email] : $email, $name) : $this->toStorage();
    }

    public function toEach(array $emails)
    {
        return $this->toStorage(array_values($emails));
    }

    public function cc($email = null, $name = null)
    {
        return func_num_args() ? $this->ccStorage($email, $name) : $this->ccStorage();
    }

    public function bcc($email = null, $name = null)
    {
        return func_num_args() ? $this->bccStorage($email, $name) : $this->bccStorage();
    }

    protected function toStorage($email = null, $name = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    protected function ccStorage($email = null, $name = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    protected function bccStorage($email = null, $name = null, $type = Obj::PROP_APPEND, $replace = false)
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