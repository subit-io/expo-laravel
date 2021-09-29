<?php

namespace NotificationChannels\ExpoPushNotifications;

use Exception;
use Subit\ExpoSdk\ExpoMessage;
use Subit\ExpoSdk\ExpoMessageTicket;
use Subit\ExpoSdk\ExpoMessageReceipt;
use Subit\ExpoSdk\Expo as ExpoTransport;
use Subit\ExpoSdk\Exceptions\ApiTransferException;
use Subit\ExpoSdk\Exceptions\ExpoApiEndpointException;
use NotificationChannels\ExpoPushNotifications\Exceptions\ExpoSdkException;
use NotificationChannels\ExpoPushNotifications\Representations\RecipientRepresentation;
use NotificationChannels\ExpoPushNotifications\Exceptions\RegisterExceptions\ExpoException;

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

    public function subscribe(RecipientRepresentation $recipient): string
    {
        return $this->register->registerRecipient($recipient);
    }

    public function unsubscribe(RecipientRepresentation $recipient): bool
    {
        return $this->register->removeRecipient($recipient);
    }

    public function removeDevice(string $token): bool
    {
        return $this->register->removeToken($token);
    }

    /**
     * @throws ExpoException
     * @throws ApiTransferException
     * @throws ExpoApiEndpointException
     * @throws ExpoSdkException
     * @throws Exceptions\RegisterExceptions\NoRecipientException
     */
    public function notify($recipients, ExpoMessage $expoMessage): array
    {
        $expoMessages = [];

        if (is_a($recipients, RecipientRepresentation::class)) {
            $recipients = [$recipients];
        }

        if (count($recipients) === 0) {
            throw new ExpoException();
        }

        //Gets expo tokens for the recipients
        $tokens = $this->register->getTokens($recipients);

        foreach ($tokens as $token) {
            $expoMessageClone = clone $expoMessage;
            $expoMessageClone->to($token);
            $expoMessages[] = $expoMessageClone;
        }

        try {
            return $this->expoTransport->sendPushNotifications($expoMessages);
        } catch (ApiTransferException $apiTransferException) {
            throw $apiTransferException;
        } catch (ExpoApiEndpointException $endpointException) {
            throw $endpointException;
        } catch (Exception $e) {
            throw new ExpoSdkException($e->getMessage());
        }
    }

    /**
     * @param ExpoMessageTicket|ExpoMessageReceipt $objectOfInterest
     */
    public function deviceWasRegistered($objectOfInterest): bool
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

        return true;
    }
}
