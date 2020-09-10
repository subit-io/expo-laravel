<?php

namespace NotificationChannels\ExpoPushNotifications;

use Subit\ExpoSdk\ExpoMessageTicket;
use Subit\ExpoSdk\ExpoMessageReceipt;
use NotificationChannels\ExpoPushNotifications\Exceptions\ExpoTransportException;
use NotificationChannels\ExpoPushNotifications\Exceptions\RegisterExceptions\ExpoException;
use NotificationChannels\ExpoPushNotifications\Representations\RecipientRepresentation;
use Subit\ExpoSdk\Expo as ExpoTransport;
use Subit\ExpoSdk\ExpoMessage;

class Expo
{
    public $expoTransport;
    public $register;

    public function __construct(ExpoRegister $expoRegister, ExpoTransport $expoTransport = null)
    {
        if (isset($expoTransport)) {
            $this->expoTransport = $expoTransport;
        } else {
            $this->expoTransport = new ExpoTransport();
        }
        $this->register = $expoRegister;
    }

    public function subscribe(RecipientRepresentation $recipient)
    {
        return $this->register->registerRecipient($recipient);
    }

    public function unsubscribe(RecipientRepresentation $recipient)
    {
        return $this->register->removeRecipient($recipient);
    }

    public function removeDevice(string $token)
    {
        return $this->register->removeToken($token);
    }

    public function notify($recipients, ExpoMessage $expoMessage, $debug = false)
    {
        $expoMessages = [];

        if (is_a($recipients, RecipientRepresentation::class)) {
            $recipients = [$recipients];
        }

        if (count($recipients) == 0) {
            throw new ExpoException();
        }

        //Gets expo tokens for the recipients
        $tokens = $this->register->getTokens($recipients);

        foreach ($tokens as $token) {
            $expoMessageClone = clone $expoMessage;
            $expoMessageClone->to($token);
            array_push($expoMessages, $expoMessageClone);
        }

        try {
            return $this->expoTransport->sendPushNotifications($expoMessages);
        } catch (\Exception $e) {
            throw new ExpoTransportException($e->getMessage());
        }
    }

    /**
     * @param ExpoMessageTicket|ExpoMessageReceipt $objectOfInterest
     */
    public function deviceWasRegistered($objectOfInterest)
    {
        $details = $objectOfInterest->getDetails();
        if (!$details) {
            return true;
        }
        $details = $objectOfInterest instanceof ExpoMessageTicket ? json_decode($details) : $details;

        if (property_exists($details, 'error')) {
            if ($details->error === 'DeviceNotRegistered') {
                return false;
            }
        }
        if (property_exists($details, 'apns')) {
            if (property_exists($details->apns, 'error')) {
                return $details->apns->error === 'DeviceNotRegistered';
            }
        }
        return true;
    }
}
