<?php


namespace NotificationChannels\ExpoPushNotifications\Test;

use Subit\ExpoSdk\ExpoMessageReceipt;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Mockery;
use NotificationChannels\ExpoPushNotifications\Exceptions\RegisterExceptions\CouldNotRemoveRecipientException;
use NotificationChannels\ExpoPushNotifications\Exceptions\RegisterExceptions\CouldNotRemoveRecipientTokenException;
use NotificationChannels\ExpoPushNotifications\Exceptions\RegisterExceptions\ExpoException;
use NotificationChannels\ExpoPushNotifications\Exceptions\RegisterExceptions\InvalidTokenException;
use NotificationChannels\ExpoPushNotifications\Expo;
use NotificationChannels\ExpoPushNotifications\ExpoRegister;
use NotificationChannels\ExpoPushNotifications\ExpoRepository;
use NotificationChannels\ExpoPushNotifications\Repositories\ExpoDatabaseDriver;
use NotificationChannels\ExpoPushNotifications\Representations\RecipientRepresentation;
use Subit\ExpoSdk\Expo as ExpoTransport;
use Subit\ExpoSdk\ExpoMessage;
use Subit\ExpoSdk\ExpoMessageTicket;

class ExpoTest extends TestCase
{
    /**
     * @var ExpoRepository
     */
    protected $repository;

    /**
     * @var ExpoRegister
     */
    protected $register;

    /**
     * @var ExpoTransport
     */
    protected $expoTransport;

    /**
     * @var Expo
     */
    protected $expo;

    /**
     * @var ExpoMessage
     */
    protected $message;
    /**
     * @var TestNotification
     */
    protected $notification;

    /**
     * @var TestNotifiableUser
     */
    protected $notifiableUser;

    public function setUp(): void
    {
        parent::setUp();

        $this->repository = new ExpoDatabaseDriver();

        $this->register = new ExpoRegister($this->repository);

        $this->expoTransport = Mockery::mock(ExpoTransport::class);
        //        $this->expo = new Expo($this->register);

        $this->expo = new Expo($this->register, $this->expoTransport);

        $this->notification = new TestNotification;

        $this->notifiableUser = new TestNotifiableUser;

        $this->setUpDatabase();
    }

    public function tearDown(): void
    {
        parent::tearDown();

        Mockery::close();
    }

    public function testSubscribe()
    {
        $recipient = RecipientRepresentation::create()
            ->type('User')
            ->id('1')
            ->deviceId('ExponentPushDeviceId[123]')
            ->token('ExponentPushToken[123]');

        $this->assertEquals($recipient->getToken(), $this->expo->subscribe($recipient));
    }

    public function testSubscribeInvalidPushToken()
    {
        $recipient = RecipientRepresentation::create()
            ->type('User')
            ->id('1')
            ->deviceId('ExponentPushDeviceId[123]')
            ->token('InvalidPushToken');

        $this->expectException(InvalidTokenException::class);

        $this->expo->subscribe($recipient);
    }

    public function testUnsubscribe()
    {
        $recipient = RecipientRepresentation::create()
            ->type('User')
            ->id('2')
            ->deviceId('ExponentPushDeviceId[123]')
            ->token('ExponentPushToken[123]');

        $this->expo->subscribe($recipient);

        $this->assertTrue($this->expo->unsubscribe($recipient));
        $this->expectException(CouldNotRemoveRecipientException::class);
        $this->expo->unsubscribe($recipient);
    }

    public function testRemoveDevice()
    {
        $recipient = RecipientRepresentation::create()
            ->type('User')
            ->id('2')
            ->deviceId('ExponentPushDeviceId[123]')
            ->token('ExponentPushToken[123]');

        $this->expo->subscribe($recipient);

        $this->assertTrue($this->expo->removeDevice($recipient->getToken()));

        $this->expectException(CouldNotRemoveRecipientTokenException::class);
        $this->expo->removeDevice($recipient->getToken());
    }

    public function testDeviceWasRegisteredWithNoDetails()
    {
        $ticket = ExpoMessageTicket::create();
        $this->assertTrue($this->expo->deviceWasRegistered($ticket));
    }

    public function testDeviceWasRegisteredWithOtherError()
    {
        $receipt = ExpoMessageReceipt::create()->details(json_decode('{"error":"anotherError"}'));
        $this->assertTrue($this->expo->deviceWasRegistered($receipt));
    }

    public function testDeviceWasRegisteredWithFailedFCM()
    {
        $ticket = ExpoMessageTicket::create()->details('{"error":"DeviceNotRegistered","fault":"developer"}');
        $this->assertFalse($this->expo->deviceWasRegistered($ticket));
    }

    public function testDeviceWasRegisteredWithFailedAPNS()
    {
        $receipt = ExpoMessageReceipt::create()->details(json_decode('{"apns":{"reason":"Unregistered","statusCode":410},"error":"DeviceNotRegistered","sentAt":1599453184}'));
        $this->assertFalse($this->expo->deviceWasRegistered($receipt));
    }

    public function testUnsubscribeWithNonExistingNotifiable()
    {
        $recipient = RecipientRepresentation::create()
            ->type('User')
            ->id('4')
            ->deviceId('ExponentPushDeviceId[123]')
            ->token('ExponentPushToken[123]');

        $this->expectException(CouldNotRemoveRecipientException::class);
        $this->expo->unsubscribe($recipient);
    }

    public function testNotifySingleRecipient()
    {
        $recipient = RecipientRepresentation::create()
            ->type('User')
            ->id('4')
            ->deviceId('ExponentPushDeviceId[zOqdVVH-Oj278YZmOgyAhd]')
            ->token('ExponentPushToken[zOqdVVH-Oj278YZmOgyAhd]');

        $this->expo->subscribe($recipient);

        $expoMessage = ExpoMessage::create();

        $ticket = ExpoMessageTicket::create()
            ->id('eee6f39d-8b2c-4496-9017-3dc10a35f5b4')
            ->status('ok');

        $ticketsMockReturn = [$ticket];

        $this->expoTransport
            ->shouldReceive('sendPushNotifications')
            ->once()
            ->andReturn($ticketsMockReturn);

        $tickets = $this->expo->notify($recipient, $expoMessage);

        /**
 * @var ExpoMessageTicket $ticket
*/
        $ticket = $tickets[0];

        $this->assertEquals('ok', $ticket->getStatus());
    }

    public function testNotifyRecipients()
    {
        $recipient = RecipientRepresentation::create()
            ->type('User')
            ->id('4')
            ->deviceId('ExponentPushDeviceId[zOqdVVH-Oj278YZmOgyAhd]')
            ->token('ExponentPushToken[zOqdVVH-Oj278YZmOgyAhd]');

        $recipient2 = RecipientRepresentation::create()
            ->type('Substitute')
            ->id('4')
            ->deviceId('ExponentPushDeviceId[zOqdVVH-Oj278YZmOgyAhd]')
            ->token('ExponentPushToken[zOqdVVH-Oj278YZmOgyAhd]');

        $this->expo->subscribe($recipient);
        $this->expo->subscribe($recipient2);

        $recipients = [$recipient, $recipient2];

        $expoMessage = ExpoMessage::create();

        $ticketReturned1 = ExpoMessageTicket::create()
            ->id('ab808283-9e8e-4723-ae1d-ab643b40a202')
            ->status('ok');

        $ticketReturned2 = ExpoMessageTicket::create()
            ->id('9676cb27-4216-48ca-b36d-ef1dd61f896z')
            ->status('ok');

        $ticketsMockReturn = [$ticketReturned1, $ticketReturned2];

        $this->expoTransport
            ->shouldReceive('sendPushNotifications')
            ->once()
            ->andReturn($ticketsMockReturn);

        $tickets = $this->expo->notify($recipients, $expoMessage);

        $this->assertEquals(count($recipients), count($tickets));
    }

    public function testNotifySingleRecipientWithMultipleDevices()
    {
        $recipientA = RecipientRepresentation::create()
            ->type('User')
            ->id('5')
            ->deviceId('ExponentPushDeviceId[1]')
            ->token('ExponentPushToken[zOqdVVH-Oj278YZmOgyAhd]');

        $recipientB = RecipientRepresentation::create()
            ->type('User')
            ->id('5')
            ->deviceId('ExponentPushDeviceId[2]')
            ->token('ExponentPushToken[zOqdVVH-Oj278YZmOgyAhd]');

        $recipients = [$recipientA, $recipientB];
        $this->expo->subscribe($recipientA);
        $this->expo->subscribe($recipientB);

        $expoMessage = ExpoMessage::create();

        $ticketReturnedA = ExpoMessageTicket::create()
            ->id('ab808283-9e8e-4723-ae1d-ab643b40a202')
            ->status('ok');

        $ticketReturnedB = ExpoMessageTicket::create()
            ->id('9676cb27-4216-48ca-b36d-ef1dd61f896z')
            ->status('ok');

        $ticketsMockReturn = [$ticketReturnedA, $ticketReturnedB];

        $this->expoTransport
            ->shouldReceive('sendPushNotifications')
            ->once()
            ->andReturn($ticketsMockReturn);

        $tickets = $this->expo->notify($recipients, $expoMessage);
        /**
         * @var ExpoMessageTicket $ticket
         */
        $this->expoTransport
            ->shouldReceive('sendPushNotifications')
            ->once()
            ->andReturn($ticketsMockReturn);

        $tickets = $this->expo->notify($recipients, $expoMessage);

        $this->assertEquals(count($recipients), count($tickets));
    }

    public function testNotifyNoRecipient()
    {
        $recipients = [];

        $expoMessage = ExpoMessage::create();

        $this->expectException(ExpoException::class);
        $this->expo->notify($recipients, $expoMessage);
    }

}

class TestNotifiableUser
{
    use Notifiable;

    public function routeNotificationForExpoPushNotifications()
    {
        return 'recipient_type';
    }

    public function getId()
    {
        return 1;
    }
}

class TestExpoNotification extends Notification
{
    public function toExpo($notifiable)
    {
        return new ExpoMessage();
    }
}
