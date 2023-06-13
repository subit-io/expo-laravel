<?php

namespace NotificationChannels\ExpoPushNotifications\Test;

use NotificationChannels\ExpoPushNotifications\ExpoPushNotificationsServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    public function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');

        $app['config']->set(
            'database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => $this->getDatabaseDirectory() . '/database.sqlite',
            'prefix' => '',
            ]
        );
    }

    /**
     * Gets the directory path for the testing database.
     *
     * @return string
     */
    public function getDatabaseDirectory(): string
    {
        return __DIR__ . '/temp';
    }

    /**
     * Get package providers.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            ExpoPushNotificationsServiceProvider::class,
        ];
    }

    /**
     * Sets up the database.
     *
     * @return void
     */
    protected function setUpDatabase()
    {
        $this->resetDatabase();

        $this->createExponentPushNotificationRecipientsTable();
    }

    /**
     * Drops the database.
     *
     * @return void
     */
    protected function resetDatabase()
    {
        file_put_contents(__DIR__ . '/temp' . '/database.sqlite', null);
    }

    /**
     * Creates the recipients table.
     *
     * @return void
     */
    protected function createExponentPushNotificationRecipientsTable()
    {
        include_once __DIR__ . '/../migrations/create_expo_notification_recipients_table.php.stub';
        include_once __DIR__ . '/../migrations/update_add_device_id_to_expo_notification_recipients_table.php.stub';

        (new \CreateExponentPushNotificationRecipientsTable())->up();
        (new \AddDeviceIdToExpoNotificationRecipientsTable())->up();
    }
}
