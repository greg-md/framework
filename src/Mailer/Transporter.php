<?php

namespace Greg\Mailer;

use Greg\Support\Engine\InternalTrait;

abstract class Transporter implements TransporterInterface
{
    use InternalTrait;

    const NEW_LINE = "\r\n";

    public function getEncodedSubject(Mail $mail)
    {
        $subject = $mail->subject();

        if ($charset = $mail->charset()) {
            $subject = '=?' . strtoupper($charset) . '?B?' . base64_encode($subject) . '?=';
        }

        return $subject;
    }

    public function fetchBody(Mail $mail)
    {
        if (sizeof($body = $mail->body()) > 1) {
            $contentType = 'multipart/alternative; boundary="' . ($boundary = uniqid('boundary')) . '"';

            $message = $this->fetchMultiPart($boundary, $body, $mail->charset());
        } else {
            $contentType = $this->fetchContentType(key($body), $mail->charset());

            $message = current($body);
        }

        return [$message, $contentType];
    }

    public function getAdditionalHeaders(Mail $mail)
    {
        $headers = array();

        list($fromEmail, $fromName) = ($mail->from() ?: [null, null]);

        if ($fromEmail) {
            $headers[] = 'From: ' . $this->emailsToString([$fromEmail => $fromName]);
        }

        list($replyToEmail, $replyToName) = ($mail->replyTo() ?: [null, null]);

        if ($replyToEmail) {
            $headers[] = 'Reply-To: ' . $this->emailsToString([$replyToEmail => $replyToName]);
        }

        if ($mimeVersion = $mail->mimeVersion()) {
            $headers[] = 'MIME-Version: ' . $mimeVersion;
        }

        if ($cc = $mail->cc()) {
            $headers[] = 'Cc: ' . $this->emailsToString($cc);
        }

        if ($bcc = $mail->bcc()) {
            $headers[] = 'Bcc: ' . $this->emailsToString($bcc);
        }

        $headers = implode(static::NEW_LINE, $headers);

        return $headers;
    }

    static public function emailsToString(array $emails)
    {
        $list = [];

        foreach($emails as $email => $name) {
            $list[] = $name ? $name . ' <' . $email . '>' : $email;
        }

        return implode(',', $list);
    }

    static public function fetchContentType($mime = 'text/plain', $charset = null)
    {
        $contentType = [$mime ?: 'text/plain'];

        if ($charset) {
            $contentType[] = 'charset=' . $charset;
        }

        return implode('; ', $contentType);
    }

    static public function fetchMultiPart($boundary, $body, $charset = null)
    {
        $parts = array();

        foreach($body as $mime => $part) {
            $partContentType = static::fetchContentType($mime, $charset);

            $parts[] = 'Content-Type: ' . $partContentType . static::NEW_LINE . static::NEW_LINE . $part;
        }

        return 'This is a multi-part message in MIME format.'
            . static::NEW_LINE
            . static::NEW_LINE
            . '--' . $boundary
            . static::NEW_LINE
            . implode(static::NEW_LINE . static::NEW_LINE . '--' . $boundary . static::NEW_LINE, $parts)
            . static::NEW_LINE
            . static::NEW_LINE
            . '--' . $boundary . '--';
    }
}