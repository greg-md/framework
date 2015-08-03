<?php

namespace Greg\NotificationCenter;

use Greg\Support\Arr;
use Greg\Engine\InternalTrait;

class Notifier extends \Greg\Support\NotificationCenter\Notifier
{
    use InternalTrait;

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

    public function renderFlash($name, $params = [])
    {
        return $this->app()->viewer()->partial($name, [
                'notifications' => $this->getFlash(),
            ] + $params);
    }

    protected function newNotification($message = null, $type = null, array $settings = [])
    {
        return Notification::create($this->appName(), $this, $message, $type, $settings);
    }
}