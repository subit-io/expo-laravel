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

    /**
     * @var TestTicket
     */
    protected $testTicket;

    public function setUp(): void
    {
        parent::setUp();

        $this->expo = Mockery::mock(Expo::class);

        $this->dispatcher = Mockery::mock(Dispatcher::class);

        $this->channel = new ExpoChannel($this->expo, $this->dispatcher);

        $this->notification = new TestNotification;

        $this->notifiable = new TestNotifiable;

        $this->testTicket = new TestTicket;
    }

    public function tearDown(): void
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
        $tickets = [$this->testTicket];

        $this->expo->shouldReceive('notify')->with($recipient, $message, true)->andReturn($tickets);
        $this->expo->shouldReceive('deviceWasRegistered')->andReturn(true);
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

    /**
     * @test
     */
    public function itRemovesTokenIfDeviceIsNotRegisteredWithFCM()
    {
        $recipient = Mockery::type(RecipientRepresentation::class);
        $message = Mockery::type(ExpoMessage::class);
        $details = '{"error":"DeviceNotRegistered","fault":"developer"}';
        $ticket = (new ExpoMessageTicket)->details($details)->token('ABCD');
        $tickets = [$ticket];

        $this->expo->shouldReceive('notify')->with($recipient, $message, true)->andReturn($tickets);
        $this->expo->shouldReceive('deviceWasRegistered')->once()->with($ticket)->andReturn(false);
        $this->expo->shouldReceive('removeDevice')->once()->with($ticket->getToken());
        $this->dispatcher->shouldReceive('dispatch')->with('expo-push-notifications', [$this->notifiable, $this->notification, $tickets]);

        $this->channel->send($this->notifiable, $this->notification);
    }

    /**
     * @test
     */
    public function itRemovesTokenIfDeviceIsNotRegisteredWithAPNS()
    {
        $recipient = Mockery::type(RecipientRepresentation::class);
        $message = Mockery::type(ExpoMessage::class);
        $details = '{"apns":{"reason":"Unregistered","statusCode":410},"error":"DeviceNotRegistered","sentAt":1599453184}';
        $ticket = (new ExpoMessageTicket)->details($details)->token('ABCD');
        $tickets = [$ticket];

        $this->expo->shouldReceive('notify')->with($recipient, $message, true)->andReturn($tickets);
        $this->expo->shouldReceive('deviceWasRegistered')->once()->with($ticket)->andReturn(false);
        $this->expo->shouldReceive('removeDevice')->once()->with($ticket->getToken());
        $this->dispatcher->shouldReceive('dispatch')->with('expo-push-notifications', [$this->notifiable, $this->notification, $tickets]);

        $this->channel->send($this->notifiable, $this->notification);
    }
}

class TestTicket
{
    private $details;

    public function getDetails()
    {
        return $this->details;
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
    public function toExpoPush($notifiable): ExpoMessage
    {
        return ExpoMessage::create()->title('Title');
    }
}
