<?php

namespace NotificationChannels\ExpoPushNotifications\Test;

use Illuminate\Events\Dispatcher;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Mockery;
use NotificationChannels\ExpoPushNotifications\Exceptions\ExpoTransportException;
use NotificationChannels\ExpoPushNotifications\Expo;
use NotificationChannels\ExpoPushNotifications\ExpoChannel;
use NotificationChannels\ExpoPushNotifications\ExpoRegister;
use NotificationChannels\ExpoPushNotifications\Repositories\ExpoDatabaseDriver;
use NotificationChannels\ExpoPushNotifications\Representations\RecipientRepresentation;
use Subit\ExpoSdk\ExpoMessage;
use Subit\ExpoSdk\ExpoMessageTicket;
use Subit\ExpoSdk\Expo as ExpoTransport;

class ChannelTest extends TestCase
{
    /**
     * @var Expo
     */
    protected $expo;

    /**
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * @var ExpoChannel
     */
    protected $channel;

    /**
     * @var TestNotification
     */
    protected $notification;

    /**
     * @var TestNotifiable
     */
    protected $notifiable;

    public function setUp()
    {
        parent::setUp();

        $this->expo = Mockery::mock(Expo::class);

        $this->dispatcher = Mockery::mock(Dispatcher::class);

        $this->channel = new ExpoChannel($this->expo, $this->dispatcher);

        $this->notification = new TestNotification;

        $this->notifiable = new TestNotifiable;
    }

    public function tearDown()
    {
        parent::tearDown();

        Mockery::close();
    }

    /**
     * @test
     */
    public function itCanSendANotification()
    {
        $recipient = Mockery::type(RecipientRepresentation::class);
        $message = Mockery::type(ExpoMessage::class);
        $tickets = [Mockery::type(ExpoMessageTicket::class)];

        $this->expo->shouldReceive('notify')->with($recipient, $message, true)->andReturn($tickets);
        $this->dispatcher->shouldReceive('dispatch')->with('expo-push-notifications', [$this->notifiable, $this->notification, $tickets]);

        $this->channel->send($this->notifiable, $this->notification);
    }

    /**
     * @test
     */
    public function itFiresFailureEventOnFailure()
    {
        $recipient = Mockery::type(RecipientRepresentation::class);
        $message = Mockery::type(ExpoMessage::class);

        $this->expo->shouldReceive('notify')->with($recipient, $message, true)->andThrow(ExpoTransportException::class, '');

        $this->dispatcher->shouldReceive('dispatch')->with(Mockery::type(NotificationFailed::class));

        $this->channel->send($this->notifiable, $this->notification);
    }
}

class TestNotifiable
{
    use Notifiable;

    public function routeNotificationForExpoPushNotifications()
    {
        return 'recipientType';
    }

    public function getKey()
    {
        return 1;
    }
}

class TestNotification extends Notification
{
    public function toExpoPush($notifiable)
    {
        return ExpoMessage::create()->title('Title');
    }
}
