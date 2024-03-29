<?php

namespace NotificationChannels\ExpoPushNotifications;

use Illuminate\Support\ServiceProvider;
use NotificationChannels\ExpoPushNotifications\Repositories\ExpoDatabaseDriver;

class ExpoPushNotificationsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->setupConfig();

        $repository = $this->getRecipientsDriver();

        $this->_shouldPublishMigrations($repository);

        $this->app->when(ExpoChannel::class)
            ->needs(Expo::class)
            ->give(
                function () use ($repository) {
                    return new Expo(new ExpoRegister($repository));
                }
            );
    }

    /**
     * Publishes the configuration files for the package.
     *
     * @return void
     */
    protected function setupConfig()
    {
        $this->publishes(
            [
                __DIR__ . '/../config/exponent-push-notifications.php' => config_path('exponent-push-notifications.php'),
            ], 'config'
        );

        $this->mergeConfigFrom(__DIR__ . '/../config/exponent-push-notifications.php', 'exponent-push-notifications');
    }

    /**
     * Gets the Expo repository driver based on config.
     *
     * @return ExpoRepository
     */
    public function getRecipientsDriver()
    {
        return new ExpoDatabaseDriver();
    }

    /**
     * Publishes the migration files needed in the package.
     *
     * @param ExpoRepository $repository
     *
     * @return void
     */
    private function _shouldPublishMigrations(ExpoRepository $repository)
    {
        $timestamp = date('Y_m_d_His', time());

        if ($repository instanceof ExpoDatabaseDriver && !class_exists('CreateExponentPushNotificationRecipientsTable')) {
            $this->publishes(
                [
                    __DIR__ . '/../migrations/create_expo_notification_recipients_table.php.stub'
                    => database_path("/migrations/{$timestamp}_create_exponent_push_notification_recipients_table.php"),
                ],
                'migrations'
            );
        }

        $this->publishes(
            [
                __DIR__ . '/../migrations/update_add_device_id_to_expo_notification_recipients_table.php.stub'
                => database_path("/migrations/{$timestamp}_add_device_id_to_expo_notification_recipients_table.php"),
            ],
            'update-migrations'
        );
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(ExpoRepository::class, get_class($this->getRecipientsDriver()));
        $this->app->bind(Expo::class, function () {
            return new Expo(new ExpoRegister($this->getRecipientsDriver()));
        });
    }
}
