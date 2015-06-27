<?php

namespace Greg\Component\NotificationCenter;


use Greg\Application\Runner;
use Greg\Event\Listener;
use Greg\Event\SubscriberInterface;
use Greg\Event\SubscriberTrait;
use Greg\Support\Engine\InternalTrait;
use Greg\Support\Server\Session;

class Notifier implements SubscriberInterface
{
    use SubscriberTrait, InternalTrait;

    public function subscribe(Listener $listener)
    {
        $listener->register([
            Runner::EVENT_INIT
        ], $this);

        return $this;
    }

    public function appInit()
    {
        // add Session instance with _flash_, _new_, _old_ params
        $flash = Session::get('_flash_');

        Session::del('_flash_');

        $flash and dd($flash);
    }

    public function toFlash(Notification $notification)
    {
        Session::setIndex('_flash_.notifications.', [
            'type' => $notification->type(),
            'message' => $notification->message(),
            'options' => $notification->options(),
        ]);

        return $this;
    }

    public function toDb(Notification $notification)
    {

    }

    public function info($message = null, array $options = [])
    {
        return Notification::create($this->appName(), $this, $message, Notification::TYPE_INFO, $options);
    }

    public function success($message = null, array $options = [])
    {
        return Notification::create($this->appName(), $this, $message, Notification::TYPE_SUCCESS, $options);
    }

    public function error($message = null, array $options = [])
    {
        return Notification::create($this->appName(), $this, $message, Notification::TYPE_ERROR, $options);
    }

    public function warning($message = null, array $options = [])
    {
        return Notification::create($this->appName(), $this, $message, Notification::TYPE_WARNING, $options);
    }

    /*
    public function multiInfo($messages, array $options = [])
    {
        foreach($messages as $message) {
            Notification::create($this->appName(), $this, $message, Notification::TYPE_INFO, $options);
        }

        return $this;
    }

    public function multiSuccess($messages, array $options = [])
    {
        foreach($messages as $message) {
            Notification::create($this->appName(), $this, $message, Notification::TYPE_SUCCESS, $options);
        }

        return $this;
    }

    public function multiError($messages, array $options = [])
    {
        foreach($messages as $message) {
            Notification::create($this->appName(), $this, $message, Notification::TYPE_ERROR, $options);
        }

        return $this;
    }

    public function multiWarning($messages, array $options = [])
    {
        foreach($messages as $message) {
            Notification::create($this->appName(), $this, $message, Notification::TYPE_WARNING, $options);
        }

        return $this;
    }
    */
}