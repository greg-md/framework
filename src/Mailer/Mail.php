<?php

namespace Greg\Mailer;

use Greg\Storage\AccessorTrait;

class Mail
{
    use AccessorTrait;

    protected $subject = null;

    protected $body = [];

    protected $from = [];

    protected $to = [];

    protected $replyTo = [];

    protected $cc = [];

    protected $bcc = [];

    protected $mimeVersion = '1.0';

    protected $charset = 'utf-8'; // iso-8859-1

    public function send(MailTransporterInterface $transporter = null)
    {
        if (!$transporter) {
            $transporter = new Transporter\BaseTransporter();
        }

        $transporter->send($this);
    }

    public function setFrom($email, $name = null)
    {
        $this->from = [$email, $name];

        return $this;
    }

    public function getFrom()
    {
        return $this->from;
    }

    public function setReplyTo($email, $name = null)
    {
        $this->replyTo = [$email, $name];

        return $this;
    }

    public function getReplyTo()
    {
        return $this->replyTo;
    }

    public function setPlainBody($body)
    {
        return $this->setBody('text/plain', is_array($body) ? implode("\n", $body) : $body);
    }

    public function getPlainBody()
    {
        return $this->getBody('text/plain');
    }

    public function setHtmlBody($body)
    {
        return $this->setBody('text/html', is_array($body) ? implode('<br />', $body) : $body);
    }

    public function getHtmlBody()
    {
        return $this->getBody('text/html');
    }

    public function setBody($mime, $body)
    {
        return $this->setToStorage($mime, $body);
    }

    public function getBody($mime)
    {
        return $this->getFromStorage($mime);
    }

    public function getAllBody()
    {
        return $this->getStorage();
    }

    public function setSubject($subject)
    {
        $this->subject = (string)$subject;

        return $this;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function clearTo()
    {
        $this->to = [];

        return $this;
    }

    public function setTo($email, $name = null)
    {
        return $this->clearTo()->addTo($email, $name);
    }

    public function setToMulti(array $emails)
    {
        return $this->clearTo()->setToMulti($emails);
    }

    public function setToEach(array $emails)
    {
        return $this->clearTo()->setToEach($emails);
    }

    public function addTo($email, $name = null)
    {
        $this->to[] = [
            [$email, $name],
        ];

        return $this;
    }

    public function addToMulti(array $emails)
    {
        $users = [];

        foreach($emails as $email => $name) {
            if (is_int($email)) {
                $email = $name;

                $name = null;
            }

            $users = [$email, $name];
        }

        $this->to[] = $users;

        return $this;
    }

    public function addToEach(array $emails)
    {
        foreach($emails as $email => $name) {
            if (is_int($email)) {
                $email = $name;

                $name = null;
            }

            $this->addTo($email, $name);
        }

        return $this;
    }

    public function getTo()
    {
        return $this->to;
    }

    public function clearCC()
    {
        $this->cc = [];

        return $this;
    }

    public function setCC($email, $name = null)
    {
        return $this->clearCC()->addCC($email, $name);
    }

    public function addCC($email, $name = null)
    {
        $this->cc[$email] = $name;

        return $this;
    }

    public function getCC()
    {
        return $this->cc;
    }

    public function clearBCC()
    {
        $this->bcc = [];

        return $this;
    }

    public function setBCC($email, $name = null)
    {
        return $this->clearBCC()->addBCC($email, $name);
    }

    public function addBCC($email, $name = null)
    {
        $this->bcc[$email] = $name;

        return $this;
    }

    public function getBCC()
    {
        return $this->bcc;
    }

    public function setMimeVersion($version)
    {
        $this->mimeVersion = (string)$version;

        return $this;
    }

    public function getMimeVersion()
    {
        return $this->mimeVersion;
    }

    public function setCharset($charset)
    {
        $this->charset = (string)$charset;

        return $this;
    }

    public function getCharset()
    {
        return $this->charset;
    }
}