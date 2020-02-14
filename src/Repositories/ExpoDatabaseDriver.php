<?php

namespace NotificationChannels\ExpoPushNotifications\Repositories;

use NotificationChannels\ExpoPushNotifications\ExpoRepository;
use NotificationChannels\ExpoPushNotifications\Models\Recipient;
use NotificationChannels\ExpoPushNotifications\Representations\RecipientRepresentation;

class ExpoDatabaseDriver implements ExpoRepository
{
    /**
     * Stores an Expo token with a given identifier.
     *
     * @param  RecipientRepresentation $recipient
     * @return bool
     */
    public function store(RecipientRepresentation $recipient): bool
    {

        $recipientModel = Recipient::firstOrCreate(
            [
            'type' => $recipient->getType(),
            'id' => $recipient->getId(),
            'token' => $recipient->getToken()
            ]
        );
        return $recipientModel instanceof Recipient;
    }

    /**
     * Retrieves an Expo token with a given identifier.
     *
     * @param  RecipientRepresentation $recipient
     * @return array
     */
    public function retrieve(RecipientRepresentation $recipient)
    {
        return Recipient::where('type', $recipient->getType())->where('id', $recipient->getId())->pluck('token')->toArray();
    }

    /**
     * Removes an Expo token with a given identifier.
     *
     * @param  RecipientRepresentation $recipient
     * @return bool
     */
    public function forget(RecipientRepresentation $recipient): bool
    {
        $query = Recipient::where('type', $recipient->getType())->where('id', $recipient->getId());

        if ($recipient->getToken()) {
            $query->where('token', $recipient->getToken());
        }

        return $query->delete() > 0;
    }
}
