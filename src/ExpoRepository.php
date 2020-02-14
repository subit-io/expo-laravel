<?php

namespace NotificationChannels\ExpoPushNotifications;

use NotificationChannels\ExpoPushNotifications\Representations\RecipientRepresentation;

interface ExpoRepository
{
    /**
     * Stores an Expo token with a given identifier
     *
     * @param  RecipientRepresentation $recipient
     * @return bool
     */
    public function store(RecipientRepresentation $recipient): bool;

    /**
     * Retrieve an Expo token with a given identifier
     *
     * @param  RecipientRepresentation $recipient
     * @return array|string|null
     */
    public function retrieve(RecipientRepresentation $recipient);

    /**
     * Removes an Expo token with a given identifier
     *
     * @param  RecipientRepresentation $recipient
     * @return bool
     */
    public function forget(RecipientRepresentation $recipient): bool;
}
