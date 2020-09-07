<?php


namespace NotificationChannels\ExpoPushNotifications\Exceptions\RegisterExceptions;


class CouldNotRemoveRecipientTokenException extends \Exception
{
    public function __construct()
    {
        parent::__construct();

        $this->message = 'Could not remove token, due to internal error.';
    }
}
