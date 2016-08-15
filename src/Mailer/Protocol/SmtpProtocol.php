<?php

namespace Greg\Mailer\Protocol;

use Greg\Tool\ErrorHandler;

class SmtpProtocol
{
    const NEW_LINE = "\r\n";

    protected $remoteAddress = null;

    protected $timeout = null;

    protected $resource = null;

    protected $debug = true;

    protected $log = [];

    public function connect($remoteAddress = 'tcp://127.0.0.1:25', $timeout = 30)
    {
        $this->remoteAddress = $remoteAddress;

        $this->timeout = $timeout;

        ErrorHandler::throwException();

        $options['ssl']['verify_peer'] = false;

        $options['ssl']['verify_peer_name'] = false;

        $socket = stream_socket_client($remoteAddress, $errorNum, $errorStr, $timeout, STREAM_CLIENT_CONNECT, stream_context_create($options));

        if ($errorNum) {
            throw new \Exception($errorStr);
        }

        ErrorHandler::restore();

        $this->socket = $socket;

        return $this;
    }

    public function getResource()
    {
        if (!is_resource($this->resource)) {
            throw new \Exception('No connection has been established.');
        }

        return $this->resource;
    }

    public function hello($host = 'localhost', $username = null, $password = null, $layer = 'ssl')
    {
        $this->expect(220, 300);

        $this->sendHello($host);

        // If a TLS session is required, commence negotiation
        if ($layer == 'tls') {
            $this->send('STARTTLS');

            $this->expect(220, 180);

            if (!stream_socket_enable_crypto($this->getResource(), true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new \Exception('Unable to connect via TLS.');
            }

            $this->sendHello($host);
        }

        if ($username) {
            $this->send('AUTH LOGIN');

            $this->expect(334);

            $this->send(base64_encode($username));

            $this->expect(334);

            $this->send(base64_encode($password));

            $this->expect(235);
        }

        return $this;
    }

    protected function expect($code, $timeout = null)
    {
        $errMsg = null;

        $code = (array)$code;

        do {
            $result = $this->receive($timeout);

            list($cmd, $more, $msg) = preg_split('/([\s-]+)/', $result, 2, PREG_SPLIT_DELIM_CAPTURE);

            if ($errMsg !== null) {
                $errMsg .= ' ' . $msg;
            } elseif ($cmd === null || !in_array($cmd, $code)) {
                $errMsg = $msg;
            }

            // The '-' message prefix indicates an information string
            // instead of a response string.
        } while (strpos($more, '-') === 0);

        if ($errMsg !== null) {
            throw new \Exception($errMsg);
        }

        return $msg;
    }

    protected function sendHello($host)
    {
        // Support for older, less-compliant remote servers.
        // Tries multiple attempts of EHLO or HELO.
        try {
            $this->send('EHLO ' . $host);

            $this->expect(250, 300);
        } catch (\Exception $e) {
            $this->send('HELO ' . $host);

            $this->expect(250, 300);
        }

        return $this;
    }

    protected function send($request)
    {
        $this->addLog($request . static::NEW_LINE);

        $result = fwrite($this->getResource(), $request . static::NEW_LINE);

        if ($result === false) {
            throw new \Exception('Could not send request to `' . $this->remoteAddress . '`.');
        }

        return $result;
    }

    protected function receive($timeout = null)
    {
        $resource = $this->getResource();

        $timeout !== null and stream_set_timeout($resource, $timeout);;

        $this->addLog($response = fgets($resource, 1024));

        // Check meta data to ensure connection is still valid
        if (!empty(stream_get_meta_data($resource)['timed_out'])) {
            throw new \Exception('`' . $this->remoteAddress . '` has timed out.');
        }

        if ($response === false) {
            throw new \Exception('Could not read from `' . $this->remoteAddress . '`.');
        }

        return $response;
    }

    public function mail($from)
    {
        $this->send('MAIL FROM:<' . $from . '>');

        $this->expect(250, 300);

        return $this;
    }

    public function recipient($to)
    {
        $this->send('RCPT TO:<' . $to . '>');

        $this->expect(array(250, 251), 300);

        return $this;
    }

    public function data($data)
    {
        $this->send('DATA');

        $this->expect(354, 120);

        foreach (explode("\n", $data) as $line) {
            if (strpos($line, '.') === 0) {
                // Escape lines prefixed with a '.'
                $line = '.' . $line;
            }
            $this->send($line);
        }

        $this->send('.');

        $this->expect(250, 600);

        return $this;
    }

    public function reset()
    {
        $this->send('RSET');

        $this->expect(array(250, 220));

        return $this;
    }

    public function disconnect()
    {
        fclose($this->getResource());

        return $this;
    }

    public function enableDebugging()
    {
        $this->debug = true;

        return $this;
    }

    public function disableDebugging()
    {
        $this->debug = false;

        return $this;
    }

    public function addLog($message)
    {
        if ($this->debug) {
            $this->log[] = $message;
        }

        return $this;
    }

    public function getLog()
    {
        return $this->log;
    }

}