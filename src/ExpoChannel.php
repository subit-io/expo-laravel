<?php

namespace NotificationChannels\ExpoPushNotifications;


use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Notifications\Notification;
use NotificationChannels\ExpoPushNotifications\Exceptions\ExpoTransportException;
use NotificationChannels\ExpoPushNotifications\Representations\RecipientRepresentation;

class ExpoChannel
{
    /**
     * @var Dispatcher
     */
    private $_dispatcher;

    /**
     * @var Expo
     */
    public $expo;

    /**
     * ExpoChannel constructor.
     *
     * @param Expo       $expo
     * @param Dispatcher $dispatcher
     */
    public function __construct(Expo $expo, Dispatcher $dispatcher)
    {
        $this->_dispatcher = $dispatcher;
        $this->expo = $expo;
    }

    /**
     * Send the given notification.
     *
     * @param mixed        $notifiable
     * @param Notification $notification
     *
     * @return array
     * @throws Exceptions\RegisterExceptions\ExpoException
     */
    public function send($notifiable, Notification $notification): array
    {
        $tickets = [];

        $recipientType = $notifiable->routeNotificationFor('Exp oPushNotifications')
            ?: $this->recipientType($notifiable);

        $recipient = RecipientRepresentation::create()
            ->type($recipientType)
            ->id($notifiable->getKey());

        try {
            $tickets = $this->expo->notify(
                $recipient,
                $notification->toExpoPush($notifiable),
                true
            );

            $this->_dispatcher->dispatch('expo-push-notifications', [$notifiable, $notification, $tickets]);

        } catch (ExpoTransportException $e) {
            $this->_dispatcher->dispatch(
                new NotificationFailed(
                    $notifiable,
                    $notification,
                    'expo-push-notifications',
                    $e->getMessage()
                )
            );
        }
        return $tickets;
    }

    public function recipientType($notifiable)
    {
        return get_class($notifiable);
    }
}
