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
     * @param RecipientRepresentation $recipient
     * @return bool
     */
    public function store(RecipientRepresentation $recipient): bool
    {
        $recipientModel = Recipient::firstOrNew(
            [
                'type' => $recipient->getType(),
                'id' => $recipient->getId(),
                'device_id' => $recipient->getDeviceId(),
            ],
        );
        $recipientModel->token = $recipient->getToken();
        $recipientModel->save();
        return $recipientModel instanceof Recipient;
    }

    /**
     * Retrieves an Expo token with a given identifier.
     *
     * @param RecipientRepresentation $recipient
     * @return array
     */
    public function retrieve(RecipientRepresentation $recipient)
    {
        return Recipient::where('type', $recipient->getType())
            ->where('id', $recipient->getId())
            ->orderByDesc('created_at')
            ->pluck('token')
            ->unique('device_id')
            ->toArray();
    }

    /**
     * Removes an Expo recipient with a given identifier.
     *
     * @param RecipientRepresentation $recipient
     * @return bool
     */
    public function forget(RecipientRepresentation $recipient): bool
    {
        return Recipient::where('type', $recipient->getType())->where('id', $recipient->getId())->delete() > 0;
    }

    /**
     * Removes an Expo token.
     *
     * @param string $token
     * @return bool
     */
    public function forgetToken(string $token): bool
    {
        return Recipient::where('token', $token)->delete() > 0;
    }
}
