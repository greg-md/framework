<?php

namespace Greg\Mailer;

interface MailTransporterInterface
{
    public function send(Mail $mail);
}