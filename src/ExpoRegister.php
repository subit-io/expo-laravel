<?php

namespace NotificationChannels\ExpoPushNotifications;

use NotificationChannels\ExpoPushNotifications\Exceptions\RegisterExceptions\CouldNotRegisterRecipientException;
use NotificationChannels\ExpoPushNotifications\Exceptions\RegisterExceptions\CouldNotRemoveRecipientException;
use NotificationChannels\ExpoPushNotifications\Exceptions\RegisterExceptions\InvalidTokenException;
use NotificationChannels\ExpoPushNotifications\Exceptions\RegisterExceptions\NoRecipientException;
use NotificationChannels\ExpoPushNotifications\Representations\RecipientRepresentation;

class ExpoRegister
{
    /**
     * Repository that manages the storage and retrieval
     *
     * @var ExpoRepository
     */
    private $_repository;

    /**
     * ExpoRegir constructor.
     *
     * @param ExpoRepository $repository
     */
    public function __construct(ExpoRepository $repository)
    {
        $this->_repository = $repository;
    }

    /**
     * Registers the given token for the given recipient
     *
     * @param  RecipientRepresentation $recipient
     * @return string
     * @throws CouldNotRegisterRecipientException
     * @throws InvalidTokenException
     */
    public function registerRecipient(RecipientRepresentation $recipient)
    {
        if (!$this->isValidExpoPushToken($recipient->getToken())) {
            throw new InvalidTokenException();
        }

        $stored = $this->_repository->store($recipient);

        if (!$stored) {
            throw new CouldNotRegisterRecipientException();
        }

        return $recipient->getToken();
    }

    /**
     * Determines if a token is a valid Expo push token
     *
     * @param string $token
     *
     * @return bool
     */
    private function isValidExpoPushToken(string $token)
    {
        return substr($token, 0, 18) === "ExponentPushToken[" && substr($token, -1) === ']';
    }

    /**
     * Removes token of a given recipient
     *
     * @param  RecipientRepresentation $recipient
     * @return bool
     * @throws CouldNotRemoveRecipientException
     */
    public function removeRecipient(RecipientRepresentation $recipient)
    {
        if (!$this->_repository->forget($recipient)) {
            throw new CouldNotRemoveRecipientException();
        }

        return true;
    }

    /**
     * Gets the tokens of the recipient
     *
     * @param  array $recipients
     * @return array
     * @throws NoRecipientException
     */
    public function getTokens(array $recipients): array
    {
        $tokens = [];

        foreach ($recipients as $recipient) {
            $retrieved = $this->_repository->retrieve($recipient);

            if (!is_null($retrieved)) {
                if (is_string($retrieved)) {
                    $tokens[] = $retrieved;
                }

                if (is_array($retrieved)) {
                    foreach ($retrieved as $token) {
                        if (is_string($token)) {
                            $tokens[] = $token;
                        }
                    }
                }
            }
        }

        if (empty($tokens)) {
            throw new NoRecipientException();
        }

        return $tokens;
    }
}
