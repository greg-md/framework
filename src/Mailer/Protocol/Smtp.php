<?php

namespace Greg\Mailer\Protocol;

use Greg\Tool\ErrorHandler;
use Greg\Tool\Obj;

class Smtp
{
    const NEW_LINE = "\r\n";

    protected $remoteAddress = 'tcp://127.0.0.1:25';

    protected $timeout = 30;

    protected $socket = null;

    protected $debug = true;

    protected $log = array();

    public function __construct($remoteAddress = null, $timeout = null)
    {
        if ($remoteAddress !== null) {
            $this->remoteAddress($remoteAddress);
        }

        if ($timeout !== null) {
            $this->timeout($timeout);
        }

        return $this;
    }

    public function connect()
    {
        ErrorHandler::throwException();

        $socket = stream_socket_client($this->remoteAddress(), $errorNum, $errorStr, $this->timeout());

        if ($errorNum) {
            throw new \Exception($errorStr);
        }

        $this->socket($socket);

        ErrorHandler::restore();

        return $this;
    }

    public function getSocket()
    {
        $socket = $this->socket();

        if (!is_resource($socket)) {
            throw new \Exception('No connection has been established to `' . $this->remoteAddress() . '`.');
        }

        return $socket;
    }

    public function hello($host = 'localhost', $username = null, $password = null, $layer = 'ssl')
    {
        $this->expect(220, 300);

        $this->sendHello($host);

        // If a TLS session is required, commence negotiation
        if ($layer == 'tls') {
            $this->send('STARTTLS');

            $this->expect(220, 180);

            if (!stream_socket_enable_crypto($this->getSocket(), true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
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

        $result = fwrite($this->getSocket(), $request . static::NEW_LINE);

        if ($result === false) {
            throw new \Exception('Could not send request to `' . $this->remoteAddress() . '`.');
        }

        return $result;
    }

    public function addLog($message)
    {
        if ($this->debug()) {
            $this->log([$message]);
        }

        return $this;
    }

    protected function receive($timeout = null)
    {
        $socket = $this->getSocket();

        $timeout !== null and $this->setTimeout($timeout);

        $this->addLog($response = fgets($socket, 1024));

        // Check meta data to ensure connection is still valid
        if (!empty(stream_get_meta_data($socket)['timed_out'])) {
            throw new \Exception('`' . $this->remoteAddress() . '` has timed out.');
        }

        if ($response === false) {
            throw new \Exception('Could not read from `' . $this->remoteAddress() . '`.');
        }

        return $response;
    }

    public function setTimeout($timeout)
    {
        return stream_set_timeout($this->getSocket(), $timeout);
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
        fclose($this->getSocket());

        return $this;
    }

    protected function remoteAddress($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    protected function timeout($value = null)
    {
        return Obj::fetchIntVar($this, $this->{__FUNCTION__}, true, ...func_get_args());
    }

    public function socket($value = null)
    {
        return Obj::fetchVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function debug($value = null)
    {
        return Obj::fetchBoolVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function log($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }
}