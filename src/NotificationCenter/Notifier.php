<?php

namespace Greg\NotificationCenter;

class Notifier
{
    public function info($message = null, array $settings = [])
    {
        return $this->newNotification($message, Notification::TYPE_INFO, $settings);
    }

    public function success($message = null, array $settings = [])
    {
        return $this->newNotification($message, Notification::TYPE_SUCCESS, $settings);
    }

    public function error($message = null, array $settings = [])
    {
        return $this->newNotification($message, Notification::TYPE_ERROR, $settings);
    }

    public function warning($message = null, array $settings = [])
    {
        return $this->newNotification($message, Notification::TYPE_WARNING, $settings);
    }

    protected function newNotification($message = null, $type = null, array $settings = [])
    {
        return new Notification($this, $message, $type, $settings);
    }
}