<?php


namespace NotificationChannels\ExpoPushNotifications\Exceptions\RegisterExceptions;


class CouldNotRegisterRecipientException extends \Exception
{
    public function __construct()
    {
        parent::__construct();

        $this->message = 'Could not register the token provided for the recipient, due to internal error.';
        $this->code = 500;
    }
}
