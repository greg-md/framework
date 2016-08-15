<?php

namespace Greg\Mailer\Transporter;

use Greg\Mailer\Mail;
use Greg\Mailer\MailTransporter;
use Greg\Mailer\Protocol\SmtpProtocol;

class SmtpTransporter extends MailTransporter
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

    public function send(Mail $mail)
    {
        $connection = $this->getConnection();

        $connection->hello($this->getName(), $this->getUsername(), $this->getPassword(), $this->getLayer());

        $headers = [
            'Subject: ' . $this->getEncodedSubject($mail),
        ];

        list($message, $contentType) = $this->fetchBody($mail);

        if ($contentType) {
            $headers[] = 'Content-type: ' . $contentType;
        }

        $headers[] = $this->getAdditionalHeaders($mail);

        $headers = implode(static::NEW_LINE, $headers);

        list($fromEmail) = ($mail->getFrom() ?: [null, null]);

        foreach($mail->getTo() as $emails) {
            $connection->mail($fromEmail);

            foreach($emails as $email => $name) {
                $connection->recipient(is_int($email) ? $name : $email);
            }

            $toHeaders = [$headers];

            $to = $this->emailsToString($emails);

            $toHeaders[] = 'To: ' . $to;

            $connection->data(implode(static::NEW_LINE, $toHeaders) . static::NEW_LINE . static::NEW_LINE . $message);

            $connection->reset();
        }

        return $this;
    }

    protected function remoteAddress()
    {
        return $this->getProtocol() . '://' . $this->getHost() . ':'. $this->getPort();
    }

    public function getConnection()
    {
        if (!$this->connection) {
            $this->connection = new SmtpProtocol();

            $this->connection->connect($this->remoteAddress(), $this->getTimeout());
        }

        return $this->connection;
    }

    public function setHost($name)
    {
        $this->host = (string)$name;

        return $this;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function setUsername($username)
    {
        $this->username = (string)$username;

        return $this;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setPassword($password)
    {
        $this->password = (string)$password;

        return $this;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPort($number)
    {
        $this->port = (int)$number;

        return $this;
    }

    public function getPort()
    {
        return $this->port;
    }

    public function setProtocol($name)
    {
        $this->protocol = (string)$name;

        return $this;
    }

    public function getProtocol()
    {
        return $this->protocol;
    }

    public function setTimeout($timeout)
    {
        $this->timeout = (int)$timeout;

        return $this;
    }

    public function getTimeout()
    {
        return $this->timeout;
    }

    public function setLayer($name)
    {
        $this->layer = (string)$name;

        return $this;
    }

    public function getLayer()
    {
        return $this->layer;
    }

    public function setName($name)
    {
        $this->name = (string)$name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }
}