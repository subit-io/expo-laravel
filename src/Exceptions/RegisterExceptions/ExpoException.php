<?php


namespace NotificationChannels\ExpoPushNotifications\Exceptions\RegisterExceptions;


class ExpoException extends \Exception
{
    public function __construct()
    {
        parent::__construct();

        $this->message = 'Recipients Resource array must not be empty.';
    }
}
