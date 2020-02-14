<?php


namespace NotificationChannels\ExpoPushNotifications\Exceptions\RegisterExceptions;


class InvalidTokenException extends \Exception
{
    public function __construct()
    {
        parent::__construct();

        $this->message = 'The token provided is not a valid expo push notification token.';
        $this->code = 422;
    }
}
