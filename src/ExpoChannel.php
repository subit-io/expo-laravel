<?php

namespace NotificationChannels\ExpoPushNotifications;


use Throwable;
use Subit\ExpoSdk\ExpoMessageTicket;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Notifications\Events\NotificationFailed;
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
     * @param Expo $expo
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
     * @param mixed $notifiable
     * @param $notification
     *
     * @return array
     */
    public function send($notifiable, $notification): array
    {
        try {

            $tickets = [];

            $recipientType = $notifiable->routeNotificationFor('ExpoPushNotifications') ?: $this->recipientType($notifiable);

            $recipient = RecipientRepresentation::create()
                ->type($recipientType)
                ->id($notifiable->getKey());

            $tickets = $this->expo->notify(
                $recipient,
                $notification->toExpoPush($notifiable),
                true
            );

            /* @var ExpoMessageTicket $ticket */
            foreach ($tickets as $ticket) {
                if (!$this->expo->deviceWasRegistered($ticket)) {
                    $this->expo->removeDevice($ticket->getToken());
                }
            }

            $this->_dispatcher->dispatch('expo-push-notifications', [$notifiable, $notification, $tickets]);

        } catch (Throwable $e) {
            $this->_dispatcher->dispatch(
                new NotificationFailed(
                    $notifiable,
                    $notification,
                    'expo-push-notifications',
                    ['message' => $e->getMessage(), 'exception' => $e]
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
