<?php

namespace Greg\NotificationCenter;

use Greg\Engine\InternalTrait;

/**
 * Class Notification
 * @package Greg\NotificationCenter
 *
 * @method Notifier notifier($var = null)
 */
class Notification extends \Greg\Support\NotificationCenter\Notification
{
    use InternalTrait;

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
}