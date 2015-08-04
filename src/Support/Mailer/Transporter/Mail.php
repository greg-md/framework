<?php

namespace Greg\Support\Mailer\Transporter;

use Greg\Support\Mailer\Transporter;
use Greg\Support\Tool\ErrorHandler;

class Mail extends Transporter
{
    public function send(\Greg\Support\Mailer\Mail $mail)
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

        foreach($mail->to() as $email => $name) {
            $to = $this->emailsToString(is_array($name) ? $name : [$email => $name]);

            if (!$to) {
                throw new \Exception('No recipients defined.');
            }

            mail($to, $subject, $message, $headers);
        }

        ErrorHandler::restore();

        return $this;
    }
}