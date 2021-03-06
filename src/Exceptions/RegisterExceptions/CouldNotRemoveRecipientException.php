<?php


namespace NotificationChannels\ExpoPushNotifications\Exceptions\RegisterExceptions;


class CouldNotRemoveRecipientException extends \Exception
{
    public function __construct()
    {
        parent::__construct();

        $this->message = 'Could not remove recipient, due to internal error.';
    }
}
