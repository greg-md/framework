<?php

namespace Greg\Component\NotificationCenter;

use Greg\Support\Engine\InternalTrait;
use Greg\Support\Obj;

class Notification
{
    use InternalTrait;

    const TYPE_INFO = 'info';

    const TYPE_SUCCESS = 'success';

    const TYPE_ERROR = 'error';

    const TYPE_WARNING = 'warning';

    protected $notifier = null;

    protected $type = null;

    protected $message = null;

    protected $settings = [];

    public function __construct(Notifier $notifier, $message = null, $type = null, array $settings = [])
    {
        $this->notifier($notifier);

        if ($message !== null) {
            $this->message($message);
        }

        if ($type !== null) {
            $this->type($type);
        }

        $this->settings($settings);
    }

    /**
     * @param $appName
     * @param Notifier $notifier
     * @param null $message
     * @param null $type
     * @param array $settings
     * @return Notification
     * @throws \Exception
     */
    static public function create($appName, Notifier $notifier, $message = null, $type = null, array $settings = [])
    {
        return static::newInstanceRef($appName, $notifier, $message, $type, $settings);
    }

    public function flash()
    {
        $this->notifier()->toFlash($this);

        return $this;
    }

    public function db()
    {
        $this->notifier()->toDb($this);

        return $this;
    }

    public function info()
    {
        return $this->type(static::TYPE_INFO);
    }

    public function success()
    {
        return $this->type(static::TYPE_SUCCESS);
    }

    public function error()
    {
        return $this->type(static::TYPE_ERROR);
    }

    public function warning()
    {
        return $this->type(static::TYPE_WARNING);
    }

    /**
     * @param Notifier $notifier
     * @return Notifier|bool
     */
    public function notifier(Notifier $notifier = null)
    {
        return Obj::fetchVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function type($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function message($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function settings($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }
}