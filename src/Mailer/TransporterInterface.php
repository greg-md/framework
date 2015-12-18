<?php

namespace Greg\Mailer;

interface TransporterInterface
{
    public function send(Mail $mail);
}