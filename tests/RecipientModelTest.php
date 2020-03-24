<?php


namespace NotificationChannels\ExpoPushNotifications\Test;


use NotificationChannels\ExpoPushNotifications\Models\Recipient;

class RecipientModelTest extends TestCase
{

    public function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    public function testDatabaseAccess()
    {
        Recipient::create(
            [
            'type' => Recipient::class,
            'id' => 1,
            'token' => 'ExpoPushToken[1]'
            ]
        );

        $this->assertDatabaseHas(
            config('exponent-push-notifications.recipients.database.table_name'), [
            'type' => Recipient::class,
            'id' => 1,
            'token' => 'ExpoPushToken[1]'
            ]
        );
    }
}
