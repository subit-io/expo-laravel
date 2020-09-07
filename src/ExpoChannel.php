<?php

namespace NotificationChannels\ExpoPushNotifications;


use Subit\ExpoSdk\ExpoMessageTicket;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Notifications\Events\NotificationFailed;
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
     * @throws Exceptions\RegisterExceptions\ExpoException
     */
    public function send($notifiable, $notification): array
    {
        $tickets = [];

        $recipientType = $notifiable->routeNotificationFor('ExpoPushNotifications') ?: $this->recipientType($notifiable);

        $recipient = RecipientRepresentation::create()
            ->type($recipientType)
            ->id($notifiable->getKey());

        try {
            $tickets = $this->expo->notify(
                $recipient,
                $notification->toExpoPush($notifiable),
                true
            );

            /* @var ExpoMessageTicket $ticket */
            foreach ($tickets as $ticket) {
                if (!$this->deviceIsRegistered($ticket->getDetails())) {
                    $this->expo->removeDevice($ticket->getToken());
                }
            }

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

    private function deviceIsRegistered($details)
    {
        if (!$details) {
            return true;
        }
        $details = json_decode($details);

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
