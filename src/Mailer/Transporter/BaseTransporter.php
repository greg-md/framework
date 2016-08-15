<?php

namespace Greg\Mailer\Transporter;

use Greg\Mailer\Mail;
use Greg\Mailer\MailTransporter;
use Greg\Tool\ErrorHandler;

class BaseTransporter extends MailTransporter
{
    public function send(Mail $mail)
    {
        $subject = $this->getEncodedSubject($mail);

        list($message, $contentType) = $this->fetchBody($mail);

        $headers = [];

        if ($contentType) {
            $headers[] = 'Content-type: ' . $contentType;
        }

        $headers[] = $this->getAdditionalHeaders($mail);

        $headers = implode(static::NEW_LINE, $headers);

        ErrorHandler::throwException();

        foreach($mail->getTo() as $emails) {
            mail($this->emailsToString($emails), $subject, $message, $headers);
        }

        ErrorHandler::restore();

        return $this;
    }
}