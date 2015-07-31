<?php

namespace Greg\Component\NotificationCenter;

use Greg\Event\Listener;
use Greg\Event\SubscriberInterface;
use Greg\Event\SubscriberTrait;
use Greg\Support\Arr;
use Greg\Engine\InternalTrait;

class Notifier implements SubscriberInterface
{
    use SubscriberTrait, InternalTrait;

    public function subscribe(Listener $listener)
    {
        $listener->register([

        ], $this);

        return $this;
    }

    public function getFlash()
    {
        $notifications = (array)$this->app()->session()->flash('notifications');

        foreach($notifications as &$notification) {
            $message = Arr::get($notification, 'message');
            $type = Arr::get($notification, 'type');
            $settings = Arr::get($notification, 'settings');

            $notification = Notification::create($this->appName(), $this, $message, $type, $settings);
        }

        return $notifications;
    }

    public function toFlash(Notification $notification)
    {
        $this->app()->session()->flashIndex('notifications.', [
            'type' => $notification->type(),
            'message' => $notification->message(),
            'settings' => $notification->settings(),
        ]);

        return $this;
    }

    public function toDb(Notification $notification)
    {

    }

    public function info($message = null, array $settings = [])
    {
        return Notification::create($this->appName(), $this, $message, Notification::TYPE_INFO, $settings);
    }

    public function success($message = null, array $settings = [])
    {
        return Notification::create($this->appName(), $this, $message, Notification::TYPE_SUCCESS, $settings);
    }

    public function error($message = null, array $settings = [])
    {
        return Notification::create($this->appName(), $this, $message, Notification::TYPE_ERROR, $settings);
    }

    public function warning($message = null, array $settings = [])
    {
        return Notification::create($this->appName(), $this, $message, Notification::TYPE_WARNING, $settings);
    }

    public function renderFlash($name, $params = [])
    {
        return $this->app()->viewer()->partial($name, [
            'notifications' => $this->getFlash(),
        ] + $params);
    }

    /*
    public function multiInfo($messages, array $settings = [])
    {
        foreach($messages as $message) {
            Notification::create($this->appName(), $this, $message, Notification::TYPE_INFO, $settings);
        }

        return $this;
    }

    public function multiSuccess($messages, array $settings = [])
    {
        foreach($messages as $message) {
            Notification::create($this->appName(), $this, $message, Notification::TYPE_SUCCESS, $settings);
        }

        return $this;
    }

    public function multiError($messages, array $settings = [])
    {
        foreach($messages as $message) {
            Notification::create($this->appName(), $this, $message, Notification::TYPE_ERROR, $settings);
        }

        return $this;
    }

    public function multiWarning($messages, array $settings = [])
    {
        foreach($messages as $message) {
            Notification::create($this->appName(), $this, $message, Notification::TYPE_WARNING, $settings);
        }

        return $this;
    }
    */
}