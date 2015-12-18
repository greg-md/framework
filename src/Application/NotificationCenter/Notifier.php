<?php

namespace Greg\Application\NotificationCenter;

use Greg\Application\Engine\InternalTrait;
use Greg\Tool\Arr;

/**
 * Class Notifier
 * @package Greg\NotificationCenter
 *
 * @method Notification info($message = null, array $settings = [])
 * @method Notification success($message = null, array $settings = [])
 * @method Notification error($message = null, array $settings = [])
 * @method Notification warning($message = null, array $settings = [])
 */
class Notifier extends \Greg\NotificationCenter\Notifier
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