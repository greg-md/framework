<?php

namespace Greg\Mailer\Transporter;

use Greg\Mailer\Mail;
use Greg\Mailer\Transporter;
use Greg\Support\Obj;

class Smtp extends Transporter
{
    protected $name = 'localhost';

    protected $host = '127.0.0.1';

    protected $port = 25;

    protected $username = null;

    protected $password = null;

    protected $timeout = 30;

    protected $protocol = 'tcp';

    protected $layer = 'ssl';

    protected $connection = null;

    public function __construct($host = null, $username = null, $password = null, $port = null, $layer = null)
    {
        if ($host !== null) {
            $this->host($host);
        }

        if ($username !== null) {
            $this->username($username);
        }

        if ($password !== null) {
            $this->password($password);
        }

        if ($port !== null) {
            $this->port($port);
        }

        if ($layer !== null) {
            $this->layer($layer);
        }

        return $this;
    }

    static public function create($appName, $host = null, $username = null, $password = null, $port = null, $layer = null)
    {
        return static::newInstanceRef($appName, $host, $username, $password, $port, $layer);
    }

    public function send(Mail $mail)
    {
        $connection = $this->getConnection();

        $connection->hello($this->name(), $this->username(), $this->password(), $this->layer());

        $headers = [
            "Subject: " . $this->getEncodedSubject($mail),
        ];

        list($message, $contentType) = $this->fetchBody($mail);

        if ($contentType) {
            $headers[] = 'Content-type: ' . $contentType;
        }

        $headers[] = $this->getAdditionalHeaders($mail);

        $headers = implode(static::NEW_LINE, $headers);

        $from = $mail->from();

        foreach($mail->to() as $email => $name) {
            $connection->mail($from);

            $recipients = is_array($name) ? $name : [$email => $name];

            foreach($recipients as $rEmail => $rName) {
                $connection->recipient($rEmail);
            }

            $toHeaders = [$headers];

            $to = $this->emailsToString($recipients);

            if (!$to) {
                throw new \Exception('No recipients defined.');
            }

            $toHeaders[] = "To: " . $to;

            $connection->data(implode(static::NEW_LINE, $toHeaders) . static::NEW_LINE . static::NEW_LINE . $message);

            $connection->reset();
        }

        return $this;
    }

    protected function fetchRemoteAddress()
    {
        return $this->protocol() . '://' . $this->host() . ':'. $this->port();
    }

    /**
     * @return \Greg\Mailer\Protocol\Smtp
     * @throws \Exception
     */
    public function getConnection()
    {
        $connection = $this->connection();

        if (!$connection) {
            $connection = \Greg\Mailer\Protocol\Smtp::create($this->appName(), $this->fetchRemoteAddress(), $this->timeout());

            $connection->connect();

            $this->connection($connection);
        }

        return $connection;
    }

    public function host($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function username($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function password($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function port($value = null)
    {
        return Obj::fetchIntVar($this, $this->{__FUNCTION__}, true, ...func_get_args());
    }

    public function protocol($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function timeout($value = null)
    {
        return Obj::fetchIntVar($this, $this->{__FUNCTION__}, true, ...func_get_args());
    }

    public function layer($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function name($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function connection($value = null)
    {
        return Obj::fetchVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }
}