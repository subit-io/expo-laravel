<?php


namespace NotificationChannels\ExpoPushNotifications\Exceptions\RegisterExceptions;


class NoRecipientException extends \Exception
{
    public function __construct()
    {
        parent::__construct();

        $this->message = 'No recipients found for this notification, make sure recipients are already registered.';
        $this->code = 404;
    }
}
