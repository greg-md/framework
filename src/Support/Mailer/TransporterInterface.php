<?php

namespace Greg\Support\Mailer;

interface TransporterInterface
{
    public function send(Mail $mail);
}